<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Expressions;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\EntityAbstract;
use Kuvardin\TinyOrm\ExpressionBuilder;
use Kuvardin\TinyOrm\Parameters;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class UnaryOperation extends ExpressionAbstract
{
    public function __construct(
        readonly public mixed $operand,
        readonly ?string $prefix = null,
        readonly ?string $postfix = null,
    )
    {

    }

    public function getQueryString(Parameters $parameters): string
    {
        return ($this->prefix ?? '') .
            ExpressionBuilder::getArithmeticOperandQueryString($this->operand, $parameters) .
            ($this->postfix ?? '');
    }
}