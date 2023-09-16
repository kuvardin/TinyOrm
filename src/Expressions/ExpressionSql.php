<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Expressions;

use Kuvardin\TinyOrm\Parameters;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
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