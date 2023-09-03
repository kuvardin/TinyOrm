<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Connection;
use Kuvardin\TinyOrm\QueryAbstract;

class Delete extends QueryAbstract
{
    public function __construct(Connection $pdo)
    {
        parent::__construct($pdo);
    }

    public function getFinalQuery(): FinalQuery
    {
        // TODO: Implement getFinalQuery() method.
    }
}