<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Generator;
use IntBackedEnum;
use Kuvardin\TinyOrm\Conditions\Condition;
use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\Conditions\ConditionsList;
use Kuvardin\TinyOrm\Enums\RuleForSavingChanges;
use Kuvardin\TinyOrm\Exception\AlreadyExists;
use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\Joins\JoinAbstract;
use Kuvardin\TinyOrm\Sorting\SortingSettings;
use Kuvardin\TinyOrm\Values\ColumnValue;
use Kuvardin\TinyOrm\Values\ValuesSet;
use PDOException;
use RuntimeException;
use StringBackedEnum;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
abstract class EntityAbstract
{
    public const COL_ID = 'id';

    /**
     * @var static[][][]
     */
    protected static array $cache = [];

    protected Connection $connection;
    protected Table $entity_table;
    protected int $id;

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

    /**
     * @throws PDOException
     * @throws AlreadyExists
     */
    public static function createByValuesSet(
        ValuesSet|array $values_set,
        Connection $connection = null,
        Table $table = null,
    ): static
    {
        $table ??= static::getEntityTableDefault();

        if ($values_set instanceof ValuesSet && !$table->isEquals($values_set->table)) {
            throw new RuntimeException("Wrong values set table: {$values_set->table->getFullName()}");
        }

        $connection ??= Connection::requireConnectionDefault();
        $data = $connection
            ->getQueryBuilder()
            ->createInsertQuery($table)
            ->setOutputExpression('*')
            ->addValuesSet(
                is_array($values_set) ? new ValuesSet($table, $values_set) : $values_set,
            )
            ->execute()
            ->fetch()
        ;

        $result = new static($connection, $table, $data);
        static::addToCache($result);
        return $result;
    }

    public static function findOneByConditions(
        ConditionAbstract|array $conditions,
        SortingSettings $sorting_settings = null,
        Connection $connection = null,
        Table $table = null,
    ): ?static
    {
        $data = self::findOneRawDataByConditions(
            conditions: is_array($conditions) ? ConditionsList::fromValuesArray($conditions) : $conditions,
            sorting_settings: $sorting_settings,
            connection: $connection,
            table: $table,
        );

        return $data === null ? null : self::upsertInCache($data, $connection, $table);
    }

    protected static function findOneRawDataByConditions(
        ConditionAbstract|array $conditions,
        SortingSettings $sorting_settings = null,
        Connection $connection = null,
        Table $table = null,
    ): ?array
    {
        $generator = self::findRawDataByConditions(
            conditions: is_array($conditions) ? ConditionsList::fromValuesArray($conditions) : $conditions,
            sorting_settings: $sorting_settings,
            limit: 1,
            connection: $connection,
            table: $table,
        );

        return $generator->valid() ? $generator->current() : null;
    }

    public static function requireOneByConditions(
        ConditionAbstract|array $conditions,
        Connection $connection = null,
        Table $table = null,
    ): static
    {
        $result = self::findOneByConditions(
            conditions: is_array($conditions) ? ConditionsList::fromValuesArray($conditions) : $conditions,
            connection: $connection,
            table: $table,
        );

        if ($result === null) {
            throw new RuntimeException('Table row not found');
        }

        return $result;
    }

    public static function checkExistsByConditions(
        ConditionAbstract|array $conditions = null,
        Connection $connection = null,
        Table $table = null,
    ): bool
    {
        $connection ??= Connection::requireConnectionDefault();
        $table ??= static::getEntityTableDefault();

        $qb = $connection
            ->getQueryBuilder()
            ->createSelectQuery()
            ->setSelectExpressions([
                SelectExpression::expression(
                    $connection->expr()->sql("COUNT(*)"),
                ),
            ])
            ->setTable($table);

        if ($conditions !== null) {
            $qb->setWhere(
                is_array($conditions) ? ConditionsList::fromValuesArray($conditions) : $conditions,
            );
        }

        $result = $qb
            ->setLimit(1)
            ->execute()
            ->fetchColumn()
        ;

        return (bool)$result;
    }

    /**
     * @param JoinAbstract[]|null $joins
     */
    public static function countByConditions(
        ConditionAbstract|array $conditions = null,
        array $joins = null,
        Connection $connection = null,
        Table $table = null,
    ): int
    {
        $connection ??= Connection::requireConnectionDefault();
        $table ??= static::getEntityTableDefault();

        $qb = $connection
            ->getQueryBuilder()
            ->createSelectQuery()
            ->setSelectExpressions([
                SelectExpression::expression(
                    $connection->expr()->sql("COUNT(*)"),
                ),
            ])
            ->setTable($table);

        if ($joins !== null) {
            $qb->setJoins($joins);
        }

        if ($conditions !== null) {
            $qb->setWhere(
                is_array($conditions) ? ConditionsList::fromValuesArray($conditions) : $conditions,
            );
        }

        $result = $qb
            ->setLimit(1)
            ->execute()
            ->fetchColumn()
        ;

        return $result === false ? 0 : $result;
    }

    public static function requireOneById(
        int $id,
        Connection $connection = null,
        Table $table = null,
        bool $use_cache = true,
    ): static
    {
        $result = self::findOneById($id, $connection, $table, $use_cache);
        if ($result === null) {
            throw new RuntimeException("Table row not found by id: $id");
        }

        return $result;
    }

    public static function findOneById(
        int $id,
        Connection $connection = null,
        Table $table = null,
        bool $use_cache = true,
    ): ?static
    {
        $table ??= static::getEntityTableDefault();

        if ($use_cache && ($item = self::findOneFromCacheById($id, $connection, $table))) {
            return $item;
        }

        return self::findOneByConditions(
            conditions: [
                EntityAbstract::COL_ID => $id,
            ],
            connection: $connection,
            table: $table,
        );
    }

    protected static function findOneFromCacheById(
        int $id,
        Connection $connection = null,
        Table $table = null,
    ): ?static
    {
        $connection ??= Connection::requireConnectionDefault();
        $table ??= static::getEntityTableDefault();
        return self::$cache[$connection->getConnectionId()][static::class][$table->getFullName()][$id] ?? null;
    }

    protected static function addToCache(self $item): void
    {
        $connection_id = $item->connection->getConnectionId();
        self::$cache[$connection_id][static::class][$item->entity_table->getFullName()][$item->getId()]
            = $item;
    }

    protected static function upsertInCache(
        array $data,
        Connection $connection = null,
        Table $table = null,
        bool $add_to_cache = true,
    ): static
    {
        $connection ??= Connection::requireConnectionDefault();

        $result = self::findOneFromCacheById($data[self::COL_ID], $connection, $table);
        if ($result !== null) {
            $result->refreshData($data);
            return $result;
        }

        $result = new static($connection, $table ?? static::getEntityTableDefault(), $data);

        if ($add_to_cache) {
            self::addToCache($result);
        }

        return $result;
    }

    public static function removeFromCache(self $item): void
    {
        $connection_id = $item->connection->getConnectionId();
        unset(self::$cache[$connection_id][static::class][$item->entity_table->getFullName()][$item->getId()]);
    }

    public static function clearCacheFull(): void
    {
        self::$cache = [];
    }

    public static function clearCache(Connection $connection = null): void
    {
        $connection ??= Connection::requireConnectionDefault();
        self::$cache[$connection->getConnectionId()][static::class] = [];
    }

    /**
     * @param JoinAbstract[] $joins
     * @return Generator<static>
     */
    public static function findByConditions(
        ConditionAbstract|array $conditions = null,
        array $joins = [],
        SortingSettings $sorting_settings = null,
        int $limit = null,
        int $offset = null,
        Connection $connection = null,
        Table $table = null,
    ): Generator
    {
        $connection ??= Connection::requireConnectionDefault();

        $generator = self::findRawDataByConditions(
            conditions: is_array($conditions) ? ConditionsList::fromValuesArray($conditions) : $conditions,
            joins: $joins,
            sorting_settings: $sorting_settings,
            limit: $limit,
            offset: $offset,
            connection: $connection,
            table: $table,
        );

        if ($generator->valid()) {
            foreach ($generator as $raw_data) {
                yield self::upsertInCache($raw_data, $connection, $table);
            }
        }
    }

    /**
     * @param JoinAbstract[] $joins
     * @return Generator<array>
     */
    protected static function findRawDataByConditions(
        ConditionAbstract|array $conditions = null,
        array $joins = [],
        SortingSettings $sorting_settings = null,
        int $limit = null,
        int $offset = null,
        Connection $connection = null,
        Table $table = null,
    ): Generator
    {
        $connection ??= Connection::requireConnectionDefault();
        $table ??= static::getEntityTableDefault();

        $qb = $connection
            ->getQueryBuilder()
            ->createSelectQuery($table)
            ->setLimit($limit)
            ->setOffset($offset)
            ->setSortingSettings($sorting_settings)
        ;

        if ($joins !== []) {
            $qb->setJoins($joins);
        }

        if ($conditions !== null) {
            $qb->setWhere(
                is_array($conditions) ? ConditionsList::fromValuesArray($conditions) : $conditions,
            );
        }

        $stmt = $qb->execute();
        while ($raw_data = $stmt->fetch()) {
            yield $raw_data;
        }
    }

    /**
     * False is row was not found in database
     */
    public function refreshData(array $data = null): bool
    {
        $data ??= self::findOneRawDataByConditions(
            conditions: [
                EntityAbstract::COL_ID => $this->id,
            ],
            connection: $this->connection,
            table: $this->entity_table,
        );

        if ($data !== null) {
            $this->__construct($this->connection, $this->entity_table, $data);
            return true;
        }

        return false;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getEntityTable(): Table
    {
        return $this->entity_table;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setFieldValue(
        Column|string $column,
        mixed &$current_value,
        mixed $new_value,
        int $type = null,
        bool $force = false,
        bool $strict = true,
    ): self
    {
        if ($new_value instanceof self) {
            $new_value = $new_value->getId();
        } elseif (
            ($new_value instanceof StringBackedEnum)
            || ($new_value instanceof IntBackedEnum)
        ) {
            $new_value = $new_value->value;
        }

        $new_value_is_scalar = is_int($new_value)
            || is_string($new_value)
            || is_bool($new_value)
            || is_float($new_value);

        if (
            $strict
            && $current_value !== null
            && $new_value_is_scalar
            && gettype($current_value) !== gettype($new_value)
        ) {
            throw new RuntimeException(
                sprintf('Wrong new value type: %s (must be %s)', gettype($new_value), gettype($current_value)),
            );
        }

        if (is_string($column)) {
            $column = $this->entity_table->getColumn($column);
        } elseif ($column->table !== null && !$this->entity_table->isEquals($column->table)) {
            throw new RuntimeException("Wrong column table: {$column->table->getFullName()}");
        }

        if (
            $force
            || $new_value instanceof ExpressionAbstract
            || $current_value !== $new_value
            || is_array($current_value)
            || is_array($new_value)
        ) {
            if ($new_value instanceof ColumnValue) {
                if (!$column->isEquals($new_value->column)) {
                    throw new RuntimeException("Unexpected column: {$new_value->column} (must be $column)");
                }

                $this->unsaved_changes[$column->name] = $new_value;
            } else {
                $this->unsaved_changes[$column->name] = new ColumnValue($column, $new_value, $type);
            }
        }

        if (
            ($new_value !== $current_value || is_array($current_value) || is_array($new_value))
            && ($new_value === null || $new_value_is_scalar)
        ) {
            $current_value = $new_value;
        }

        return $this;
    }

    /**
     * @throws AlreadyExists
     * @throws PDOException
     */
    public function saveChanges(bool $apply_to_current_object = true, array &$new_entity_data = null): self
    {
        if ($this->unsaved_changes !== []) {
            $new_entity_data = $this->connection
                ->getQueryBuilder()
                ->createUpdateQuery($this->entity_table)
                ->setValues($this->unsaved_changes)
                ->setWhere(new Condition(self::COL_ID, $this->id))
                ->appendOutputExpression(SelectExpression::allColumns())
                ->execute()
                ->fetch()
            ;

            $this->unsaved_changes = [];

            if ($apply_to_current_object) {
                $this->refreshData($new_entity_data);
            }
        }

        return $this;
    }

    public function __destruct()
    {
        if ($this->unsaved_changes !== []) {
            switch ($this->connection->rule_for_saving_changes) {
                case RuleForSavingChanges::ThrowExceptionInDestructor:
                    throw new RuntimeException(
                        sprintf(
                            'Entity %s with ID %d has unsaved changes',
                            static::class,
                            $this->id
                        ),
                    );

                case RuleForSavingChanges::SaveInDestructor:
                    $this->saveChanges(false);
                    break;

                case RuleForSavingChanges::DoNothing:
                    // Doing nothing ;)
                    break;

                default:
                    throw new RuntimeException('Unknown rule for saving changes');
            }
        }

        if ($this->connection->remove_entity_from_cache_on_destructor) {
            static::removeFromCache($this);
        }
    }
}