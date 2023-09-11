<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use PDO;
use RuntimeException;
use Kuvardin\TinyOrm\Expressions\BinaryOperation;
use Kuvardin\TinyOrm\Expressions\UnaryOperation;

class ExpressionBuilder
{
    public function binaryOperation(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
        string $operator,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, $operator);
    }
    public function unaryOperation(
        Column|ExpressionAbstract|int|float $operand,
        string $prefix = null,
        string $postfix = null,
    ): UnaryOperation
    {
        return new UnaryOperation($operand, $prefix, $postfix);
    }
    
    public function sum(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '+');
    }

    public function subtraction(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '-');
    }

    public function multiplication(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '*');
    }

    public function division(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '/');
    }

    public function modulo(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '%');
    }

    public function exponentiation(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '^');
    }

    public function bitwiseAnd(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '&');
    }

    public function bitwiseOr(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '|');
    }

    public function bitwiseXor(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '#');
    }

    public function bitwiseShiftLeft(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '<<');
    }

    public function bitwiseShiftRight(
        Column|ExpressionAbstract|int|float $operand_first,
        Column|ExpressionAbstract|int|float $operand_second,
    ): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '>>');
    }

    public function squareRoot(
        Column|ExpressionAbstract|int|float $operand,
    ): UnaryOperation
    {
        return new UnaryOperation($operand, '|/');
    }

    public function cubeRoot(
        Column|ExpressionAbstract|int|float $operand,
    ): UnaryOperation
    {
        return new UnaryOperation($operand, '||/');
    }

    public function factorial(
        Column|ExpressionAbstract|int|float $operand,
    ): UnaryOperation
    {
        return new UnaryOperation($operand, 'factorial(', ')');
    }

    public function abs(
        Column|ExpressionAbstract|int|float $operand,
    ): UnaryOperation
    {
        return new UnaryOperation($operand, 'abs(', ')');
    }

    public function bitwiseNot(
        Column|ExpressionAbstract|int|float $operand,
    ): UnaryOperation
    {
        return new UnaryOperation($operand, '~');
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