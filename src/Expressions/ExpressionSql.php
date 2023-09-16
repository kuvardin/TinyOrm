<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Expressions;

use Kuvardin\TinyOrm\Parameters;

class ExpressionSql extends ExpressionAbstract
{
    public function __construct(
        readonly public string $sql,
    )
    {

    }

    public function getQueryString(Parameters $parameters): string
    {
        return $this->sql;
    }
}