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

    public function column(string $name, ?Table $table = null): Column
    {
        return new Column($name, $table);
    }

    public function binaryOperation(string $operator, array $operands): BinaryOperation
    {
        return new BinaryOperation($operator, $operands);
    }

    public function unaryOperation(mixed $operand, ?string $prefix = null, ?string $postfix = null): UnaryOperation
    {
        return new UnaryOperation($operand, $prefix, $postfix);
    }

    public function sum(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('+', $operands);
    }

    public function subtraction(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('-', $operands);
    }

    public function multiplication(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('*', $operands);
    }

    public function division(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('/', $operands);
    }

    public function modulo(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('%', $operands);
    }

    public function exponentiation(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('^', $operands);
    }

    public function bitwiseAnd(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('&', $operands);
    }

    public function bitwiseOr(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('|', $operands);
    }

    public function bitwiseXor(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('#', $operands);
    }

    public function bitwiseShiftLeft(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('<<', $operands);
    }

    public function bitwiseShiftRight(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('>>', $operands);
    }

    public function or(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('OR', $operands);
    }

    public function and(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('AND', $operands);
    }

    public function less(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('<', $operands);
    }

    public function lessOrEqual(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('<=', $operands);
    }

    public function greater(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('>', $operands);
    }

    public function greaterOrEqual(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('>=', $operands);
    }

    public function equal(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('=', $operands);
    }

    public function notEqual(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('<>', $operands);
    }

    public function in(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('IN', $operands);
    }

    public function notIn(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('NOT IN', $operands);
    }

    public function like(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('like', $operands);
    }

    public function ilike(mixed ...$operands): BinaryOperation
    {
        return new BinaryOperation('ilike', $operands);
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
        if (is_int($operand) || is_float($operand)) {
            return (string)$operand;
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