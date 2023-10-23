<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\Connection;
use Kuvardin\TinyOrm\Exception\AlreadyExists;
use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Parameters;
use PDOException;
use PDOStatement;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
abstract class QueryAbstract extends ExpressionAbstract
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    abstract public function getFinalQuery(Parameters $parameters = null): FinalQuery;

    /**
     * @throws PDOException
     * @throws AlreadyExists
     */
    final public function execute(Parameters $parameters = null): PDOStatement
    {
        return $this->connection->executeFinalQuery($this->getFinalQuery($parameters));
    }

    final public function getQueryString(Parameters $parameters): string
    {
        return $this->getFinalQuery($parameters)->value;
    }
}