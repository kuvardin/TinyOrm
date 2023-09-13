<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Conditions\Condition;
use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\Conditions\ConditionsList;
use Kuvardin\TinyOrm\Enums\Operator;
use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\Values\ColumnValue;
use Kuvardin\TinyOrm\Values\ValuesSet;
use PDOException;
use RuntimeException;

abstract class EntityAbstract
{
    public const COL_ID = 'id';

    /**
     * @var static[][][]
     */
    protected static array $cache = [];

    protected Connection $connection;
    public readonly Table $entity_table;

    public readonly int $id;

    /**
     * @var ColumnValue[]
     */
    protected array $unsaved_changes = [];

    public function __construct(Connection $connection, Table $table, array $data)
    {
        $this->connection = $connection;
        $this->entity_table = $table;
        $this->id = $data[self::COL_ID];
    }

    abstract public static function getEntityTableDefault(): Table;

    public function setFieldValue(
        Column|string $column,
        mixed &$current_value,
        mixed $new_value,
        int $type = null,
        bool $force = false,
    ): self
    {
        if (is_string($column)) {
            $column = $this->entity_table->getColumn($column);
        } elseif ($column->table !== null && !$this->entity_table->isEquals($column->table)) {
            throw new RuntimeException("Wrong column table: {$column->table->getFullName()}");
        }

        if ($force || $current_value instanceof ExpressionAbstract || $current_value !== $new_value) {
            if ($new_value instanceof ColumnValue) {
                if (!$column->isEquals($new_value->column)) {
                    throw new RuntimeException("Unexpected column: {$new_value->column} (must be $column)");
                }

                $this->unsaved_changes[$column->name] = $new_value;
            } else {
                $this->unsaved_changes[$column->name] = new ColumnValue($column, $new_value, $type);
            }
        }

        return $this;
    }

    public function saveChanges(): self
    {
        if ($this->unsaved_changes !== []) {
            $data = $this->connection
                ->getQueryBuilder()
                ->createUpdateQuery($this->entity_table)
                ->setValues($this->unsaved_changes)
                ->setOutputExpression('*')
                ->execute()
                ->fetch()
            ;

            $this->unsaved_changes = [];
            $this->__construct($this->connection, $this->entity_table, $data);
        }

        return $this;
    }

    /**
     * @throws PDOException
     */
    public static function createByValuesSet(Connection $connection, ValuesSet $values_set, Table $table = null): static
    {
        $table ??= static::getEntityTableDefault();

        if (!$table->isEquals($values_set->table)) {
            throw new RuntimeException("Wrong values set table: {$values_set->table->getFullName()}");
        }

        $data = $connection
            ->getQueryBuilder()
            ->createInsertQuery($table)
            ->setOutputExpression('*')
            ->addValuesSet($values_set)
            ->execute()
            ->fetch()
        ;

        return new static($connection, $table, $data);
    }

    public static function createByValuesArray(Connection $connection, array $values_array, Table $table = null): static
    {
        $table ??= static::getEntityTableDefault();
        return self::createByValuesSet($connection, new ValuesSet($table, $values_array), $table);
    }

    public static function findOneByConditions(
        Connection $connection,
        ConditionAbstract $conditions,
        Table $table = null,
    ): ?static
    {
        $table ??= static::getEntityTableDefault();

        $result = $connection
            ->getQueryBuilder()
            ->createSelectQuery()
            ->from($table)
            ->where($conditions)
            ->limit(1)
            ->execute()
            ->fetch()
        ;

        if ($result === false) {
            return null;
        }

        return new static($connection, $table, $result);
    }

    public static function requireOneByConditions(
        Connection $connection,
        ConditionAbstract $conditions,
        Table $table = null,
    ): static
    {
        $result = self::findOneByConditions($connection, $conditions, $table);
        if ($result === null) {
            throw new RuntimeException('Table row not found');
        }

        return $result;
    }

    public static function checkExistsByConditions(
        Connection $connection,
        ConditionAbstract $conditions = null,
        Table $table = null,
    ): bool
    {
        $table ??= static::getEntityTableDefault();

        $qb = $connection
            ->getQueryBuilder()
            ->createSelectQuery()
            ->selectExpressionsSql(["COUNT(*)"])
            ->from($table);

        if ($conditions !== null) {
            $qb->where($conditions);
        }

        $result = $qb
            ->limit(1)
            ->execute()
            ->fetchColumn()
        ;

        return !($result === false);
    }

    public static function countByConditions(
        Connection $connection,
        ConditionAbstract $conditions = null,
        Table $table = null,
    ): int
    {
        $table ??= static::getEntityTableDefault();

        $qb = $connection
            ->getQueryBuilder()
            ->createSelectQuery()
            ->selectExpressionsSql(["COUNT(*)"])
            ->from($table);

        if ($conditions !== null) {
            $qb->where($conditions);
        }

        $result = $qb
            ->limit(1)
            ->execute()
            ->fetchColumn()
        ;

        return $result === false ? 0 : $result;
    }

    public static function requireOneById(
        Connection $connection,
        int $id,
        Table $table = null,
        bool $use_cache = true,
    ): static
    {
        $result = self::findOneById($connection, $id, $table, $use_cache);
        if ($result === null) {
            throw new RuntimeException("Table row not found by id: $id");
        }

        return $result;
    }

    public static function findOneById(
        Connection $connection,
        int $id,
        Table $table = null,
        bool $use_cache = true,
    ): ?static
    {
        $table ??= static::getEntityTableDefault();

        if ($use_cache && ($item = self::getFromCacheById($connection, $id, $table))) {
            return $item;
        }

        $result = self::findOneByConditions(
            connection: $connection,
            conditions: ConditionsList::fromValuesArray([
                EntityAbstract::COL_ID => $id,
            ]),
            table: $table,
        );

        if ($result !== null) {
            self::addToCache($result);
        }

        return $result;
    }

    protected static function getFromCacheById(Connection $connection, int $id, Table $table = null): ?static
    {
        $table ??= static::getEntityTableDefault();
        return self::$cache[$connection->getConnectionId()][static::class][$table->getFullName()][$id] ?? null;
    }

    protected static function addToCache(self $item): void
    {
        self::$cache[$item->connection->getConnectionId()][static::class][$item->entity_table->getFullName()][$item->id]
            = $item;
    }

    public static function clearCacheFull(): void
    {
        self::$cache = [];
    }

    public static function clearCache(Connection $connection): void
    {
        self::$cache[$connection->getConnectionId()][static::class] = [];
    }
}