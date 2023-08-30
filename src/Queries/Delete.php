<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\CustomPdo;
use Kuvardin\TinyOrm\QueryAbstract;

class Delete extends QueryAbstract
{
    public function __construct(Pdo $pdo)
    {
        parent::__construct($pdo);
    }

    public function getFinalQuery(): FinalQuery
    {
        // TODO: Implement getFinalQuery() method.
    }
}