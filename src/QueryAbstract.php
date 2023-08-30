<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use PDOStatement;

abstract class QueryAbstract
{
    protected CustomPdo $pdo;

    public function __construct(CustomPdo $pdo)
    {
        $this->pdo = $pdo;
    }

    abstract public function getFinalQuery(): FinalQuery;

    final public function execute(): PDOStatement
    {
        return $this->pdo->executeFinalQuery($this->getFinalQuery());
    }
}