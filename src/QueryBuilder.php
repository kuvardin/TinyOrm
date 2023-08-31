<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Queries\Delete;
use Kuvardin\TinyOrm\Queries\Insert;
use Kuvardin\TinyOrm\Queries\Select;

class QueryBuilder
{
    public function __construct(
        protected CustomPdo $pdo,
    )
    {

    }

    public function createSelectQuery(): Select
    {
        return new Select($this->pdo);
    }

    public function createInsertQuery(Table $into): Insert
    {
        return new Insert($this->pdo, $into);
    }

    public function createDeleteQuery(): Delete
    {
        return new Delete($this->pdo);
    }
}