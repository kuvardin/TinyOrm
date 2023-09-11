<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Expressions;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\Expressions;
use Kuvardin\TinyOrm\Parameters;

class Multiplication extends ExpressionAbstract
{
    public function __construct(
        readonly public Column|ExpressionAbstract|int|float $operand_first,
        readonly public Column|ExpressionAbstract|int|float $operand_second,
    )
    {

    }

    public function getQueryString(Parameters $parameters): string
    {
        return Expressions::getArithmeticOperandQueryString($this->operand_first, $parameters) . ' * ' .
            Expressions::getArithmeticOperandQueryString($this->operand_second, $parameters);
    }
}