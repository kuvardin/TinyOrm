<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Conditions\Condition;
use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\Enums\Operator;

abstract class EntityAbstract
{
    public const COL_ID = 'id';

    protected Connection $pdo;
    public readonly Table $entity_table;
    public readonly int $id;

    /**
     * @var static[][][]
     */
    protected static array $cache = [];

    public function __construct(Connection $pdo, Table $table, array $data)
    {
        $this->pdo = $pdo;
        $this->entity_table = $table;
        $this->id = $data[self::COL_ID];
    }

    abstract public static function getEntityTableDefault(): Table;

    public static function findOneByConditions(
        Connection $pdo,
        ConditionAbstract $conditions,
        Table $table = null,
    ): ?static
    {
        $table ??= static::getEntityTableDefault();

        $result = $pdo
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

        return new static($pdo, $table, $result);
    }

    public static function requireOneById(
        Connection $pdo,
        int $id,
        Table $table = null,
        bool $use_cache = true,
    ): EntityAbstract
    {
        return self::findOneById($pdo, $id, $table, $use_cache);
    }

    public static function findOneById(
        Connection $pdo,
        int $id,
        Table $table = null,
        bool $use_cache = true,
    ): ?EntityAbstract
    {
        $table ??= static::getEntityTableDefault();

        if ($use_cache && ($item = self::getFromCacheById($pdo, $table, $id))) {
            return $item;
        }

        $condition = new Condition($table->getColumn(EntityAbstract::COL_ID), $id, Operator::Equals);
        $result = self::findOneByConditions($pdo, $condition, $table);

        if ($result !== null) {
            self::addToCache($result);
        }

        return $result;
    }

    protected static function getFromCacheById(Connection $pdo, Table $table, int $id): ?static
    {
        return self::$cache[$pdo->getConnectionId()][$table->getFullName()][$id] ?? null;
    }

    protected static function addToCache(self $item): void
    {
        self::$cache[$item->pdo->getConnectionId()][$item->entity_table->getFullName()][$item->id] = $item;
    }
}