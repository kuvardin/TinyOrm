<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Conditions\Condition;
use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\Enums\Operator;
use Kuvardin\TinyOrm\Values\ColumnValue;
use Kuvardin\TinyOrm\Values\ValuesSet;
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
    protected array $changes = [];

    public function __construct(Connection $connection, Table $table, array $data)
    {
        $this->connection = $connection;
        $this->entity_table = $table;
        $this->id = $data[self::COL_ID];
    }

    abstract public static function getEntityTableDefault(): Table;

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

    public static function requireOneById(
        Connection $connection,
        int $id,
        Table $table = null,
        bool $use_cache = true,
    ): EntityAbstract
    {
        return self::findOneById($connection, $id, $table, $use_cache);
    }

    public static function findOneById(
        Connection $connection,
        int $id,
        Table $table = null,
        bool $use_cache = true,
    ): ?EntityAbstract
    {
        $table ??= static::getEntityTableDefault();

        if ($use_cache && ($item = self::getFromCacheById($connection, $table, $id))) {
            return $item;
        }

        $condition = new Condition($table->getColumn(EntityAbstract::COL_ID), $id, Operator::Equals);
        $result = self::findOneByConditions($connection, $condition, $table);

        if ($result !== null) {
            self::addToCache($result);
        }

        return $result;
    }

    protected static function getFromCacheById(Connection $connection, Table $table, int $id): ?static
    {
        return self::$cache[$connection->getConnectionId()][$table->getFullName()][$id] ?? null;
    }

    protected static function addToCache(self $item): void
    {
        self::$cache[$item->connection->getConnectionId()][$item->entity_table->getFullName()][$item->id] = $item;
    }

    public static function clearCacheFull(): void
    {
        self::$cache = [];
    }
}