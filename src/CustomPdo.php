<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class CustomPdo extends PDO
{
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

    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this);
    }
}