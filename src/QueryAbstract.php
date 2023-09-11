<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use PDOException;
use PDOStatement;

abstract class QueryAbstract
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    abstract public function getFinalQuery(Parameters $parameters = null): FinalQuery;

    /**
     * @throws PDOException
     */
    final public function execute(Parameters $parameters = null): PDOStatement
    {
        return $this->connection->executeFinalQuery($this->getFinalQuery($parameters));
    }
}