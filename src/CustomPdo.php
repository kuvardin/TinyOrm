<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class CustomPdo extends PDO
{
    protected ?QueryBuilder $query_builder = null;
    private ?string $connection_id = null;

    /**
     * @var string[]
     */
    private static array $connection_ids = [];

    public function getConnectionId(): string
    {
        if ($this->connection_id === null) {
            do {
                $this->connection_id = (string)rand(0, PHP_INT_MAX);
            } while (in_array($this->connection_id, self::$connection_ids));

            self::$connection_ids[] = $this->connection_id;
        }

        return $this->connection_id;
    }

    /**
     * @throws PDOException
     */
    public function executeFinalQuery(FinalQuery $query): PDOStatement
    {
        $stmt = $this->prepare($query->value);
        if ($stmt === false) {
            throw new RuntimeException("Error preparing query: {$query->value}");
        }

        $stmt->execute($query->parameters?->toArray());
        return $stmt;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->query_builder ??= new QueryBuilder($this);
    }
}