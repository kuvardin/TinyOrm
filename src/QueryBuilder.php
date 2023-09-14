<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Queries\Delete;
use Kuvardin\TinyOrm\Queries\Insert;
use Kuvardin\TinyOrm\Queries\Select;
use Kuvardin\TinyOrm\Queries\Update;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class QueryBuilder
{
    public function __construct(
        protected Connection $connection,
    )
    {

    }

    public function createSelectQuery(Table $table = null): Select
    {
        return new Select($this->connection, $table);
    }

    public function createInsertQuery(Table $table): Insert
    {
        return new Insert($this->connection, $table);
    }

    public function createDeleteQuery(Table $table): Delete
    {
        return new Delete($this->connection, $table);
    }

    public function createUpdateQuery(Table $table): Update
    {
        return new Update($this->connection, $table);
    }
}