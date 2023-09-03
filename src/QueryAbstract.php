<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use PDOStatement;

abstract class QueryAbstract
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    abstract public function getFinalQuery(Parameters $parameters = null): FinalQuery;

    final public function execute(): PDOStatement
    {
        return $this->connection->executeFinalQuery($this->getFinalQuery());
    }
}