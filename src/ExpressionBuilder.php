<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\Expressions\ExpressionSql;
use PDO;
use RuntimeException;
use Kuvardin\TinyOrm\Expressions\BinaryOperation;
use Kuvardin\TinyOrm\Expressions\UnaryOperation;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class ExpressionBuilder
{
    public function sql(string $sql): ExpressionSql
    {
        return new ExpressionSql($sql);
    }

    public function count(mixed $operand_first): UnaryOperation
    {
        return new UnaryOperation($operand_first, 'COUNT(', ')');
    }

    public function column(string $name, Table $table = null): Column
    {
        return new Column($name, $table);
    }

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

    public function in(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, 'IN');
    }

    public function notIn(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, 'NOT IN');
    }

    public function like(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, 'like');
    }

    public function ilike(mixed $operand_first, mixed $operand_second): BinaryOperation
    {
        return new BinaryOperation($operand_first, $operand_second, 'ilike');
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

    public function isNull(Column $operand): UnaryOperation
    {
        return new UnaryOperation($operand, postfix: 'IS NULL');
    }

    public function isNotNull(Column $operand): UnaryOperation
    {
        return new UnaryOperation($operand, postfix: 'IS NOT NULL');
    }

    public static function getArithmeticOperandQueryString(mixed $operand, Parameters $parameters): string
    {
        if (is_int($operand)) {
            return $parameters->pushValue($operand, PDO::PARAM_INT);
        }

        if (is_float($operand)) {
            return $parameters->pushValue($operand);
        }

        if (is_string($operand)) {
            return $parameters->pushValue($operand, PDO::PARAM_STR);
        }

        if (is_bool($operand)) {
            return $parameters->pushValue($operand, PDO::PARAM_BOOL);
        }

        if ($operand instanceof EntityAbstract) {
            return $parameters->pushValue($operand->getId(), PDO::PARAM_INT);
        }

        if ($operand instanceof Column) {
            return $operand->getFullName(true);
        }

        if ($operand instanceof ExpressionAbstract) {
            return '(' . $operand->getQueryString($parameters) . ')';
        }

        if (is_array($operand)) {
            $array_items = array_map(
                static fn(mixed $value) => self::getArithmeticOperandQueryString($value, $parameters),
                $operand,
            );

            return '(' . implode(', ', $array_items) . ')';
        }

        throw new RuntimeException('Unexpected operand type: ' . gettype($operand));
    }
}