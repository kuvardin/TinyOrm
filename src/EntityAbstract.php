<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use RuntimeException;

abstract class EntityAbstract
{
    /**
     * @var static[]
     */
    private static array $cache = [];

    public const COL_ID = 'id';

    protected int $id;

    public static Table $table;

    public function __construct(array $data)
    {
        $this->id = $data[self::COL_ID];
        if (static::$table === null) {
            throw new RuntimeException('Table was not set');
        }
    }

    abstract public static function getSchema(): string;

    abstract public static function getTable(): string;

    public static function findOneById(CustomPdo $custom_pdo, int $id): ?static
    {
        App::pdo()
            ->createQueryBuilder()
            ->select()
            ->execute()
            ->fetch()
        ;
    }
    abstract public static function requireOneById(int $id): ?static;

    public static function createWithFieldsValues(?int $id, array $data): static
    {

    }

    
    final public function getId(): int
    {
        return $this->id;
    }
}