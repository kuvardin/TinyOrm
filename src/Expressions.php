<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use PDO;
use RuntimeException;

class Expressions
{
    public static function sum(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): Expressions\Sum
    {
        return new Expressions\Sum($operand_first, $operand_second);
    }

    public static function multiplication(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): Expressions\Multiplication
    {
        return new Expressions\Multiplication($operand_first, $operand_second);
    }

    public static function getArithmeticOperandQueryString(
        Column|ExpressionAbstract|int|float $operand,
        Parameters $parameters,
    ): string
    {
        if (is_int($operand)) {
            return $parameters->pushValue($operand, PDO::PARAM_INT);
        }

        if (is_float($operand)) {
            return $parameters->pushValue($operand);
        }

        if ($operand instanceof ExpressionAbstract) {
            return '(' . $operand->getQueryString($parameters) . ')';
        }

        if ($operand instanceof Column) {
            return $operand->getFullName();
        }

        throw new RuntimeException('Unexpected operand type: ' . gettype($operand));
    }
}