<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Expressions;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\ExpressionBuilder;
use Kuvardin\TinyOrm\Parameters;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class BinaryOperation extends ExpressionAbstract
{
    public function __construct(
        readonly public Column|ExpressionAbstract|int|float $operand_first,
        readonly public Column|ExpressionAbstract|int|float $operand_second,
        readonly string $operator,
    )
    {

    }

    public function getQueryString(Parameters $parameters): string
    {
        return ExpressionBuilder::getArithmeticOperandQueryString($this->operand_first, $parameters) .
            ' ' . $this->operator . ' ' .
            ExpressionBuilder::getArithmeticOperandQueryString($this->operand_second, $parameters);
    }
}