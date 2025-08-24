<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Expressions;

use Kuvardin\TinyOrm\ExpressionBuilder;
use Kuvardin\TinyOrm\Parameters;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class BinaryOperation extends ExpressionAbstract
{
    public function __construct(
        readonly public string $operator,
        readonly public array $operands,
    )
    {

    }

    public function getQueryString(Parameters $parameters): string
    {
        return implode(
            " {$this->operator} ",
            array_map(
                static fn(mixed $o) => ExpressionBuilder::getArithmeticOperandQueryString($o, $parameters),
                $this->operands,
            ),
        );
    }
}