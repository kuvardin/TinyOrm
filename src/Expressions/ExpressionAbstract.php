<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Expressions;

use Kuvardin\TinyOrm\Parameters;

abstract class ExpressionAbstract
{
    abstract public function getQueryString(Parameters $parameters): string;
}