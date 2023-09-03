<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use PDOStatement;

abstract class QueryAbstract
{
    protected Connection $pdo;

    public function __construct(Connection $pdo)
    {
        $this->pdo = $pdo;
    }

    abstract public function getFinalQuery(Parameters $parameters = null): FinalQuery;

    final public function execute(): PDOStatement
    {
        return $this->pdo->executeFinalQuery($this->getFinalQuery());
    }
}