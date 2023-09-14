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
    public function binaryOperation(mixed $operand_first, mixed $operand_second, string $operator): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, $operator);
    }
    public function unaryOperation(mixed $operand, string $prefix = null, string $postfix = null): UnaryOperation
    {
        return new UnaryOperation($operand, $prefix, $postfix);
    }
    
    public function sum(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '+');
    }

    public function subtraction(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '-');
    }

    public function multiplication(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '*');
    }

    public function division(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '/');
    }

    public function modulo(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '%');
    }

    public function exponentiation(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '^');
    }

    public function bitwiseAnd(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '&');
    }

    public function bitwiseOr(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '|');
    }

    public function bitwiseXor(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '#');
    }

    public function bitwiseShiftLeft(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '<<');
    }

    public function bitwiseShiftRight(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '>>');
    }

    public function or(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, 'OR');
    }

    public function and(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, 'AND');
    }

    public function less(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '<');
    }

    public function lessOrEqual(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '<=');
    }

    public function greater(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '>');
    }

    public function greaterOrEqual(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '>=');
    }

    public function equal(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '=');
    }

    public function notEqual(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, '<>');
    }

    public function squareRoot(mixed $operand): UnaryOperation
    {
        return new UnaryOperation($operand, '|/');
    }

    public function cubeRoot(mixed $operand): UnaryOperation
    {
        return new UnaryOperation($operand, '||/');
    }

    public function factorial(mixed $operand): UnaryOperation
    {
        return new UnaryOperation($operand, 'factorial(', ')');
    }

    public function abs(mixed $operand): UnaryOperation
    {
        return new UnaryOperation($operand, 'abs(', ')');
    }

    public function bitwiseNot(mixed $operand): UnaryOperation
    {
        return new UnaryOperation($operand, '~');
    }

    public static function getArithmeticOperandQueryString(mixed $operand, Parameters $parameters): string
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