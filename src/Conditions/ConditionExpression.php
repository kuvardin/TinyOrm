<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Conditions;

use Kuvardin\TinyOrm\Enums\LogicalOperator;
use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\Parameters;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class ConditionExpression extends ConditionAbstract
{
    public function __construct(
        public ExpressionAbstract $expression,
        ?LogicalOperator $prefix = null,
        ?bool $invert = null,
    )
    {
        parent::__construct($prefix, $invert);
    }

    public function getQueryString(Parameters $parameters): string
    {
        $result = $this->expression->getQueryString($parameters);
        return $this->invert ? "NOT ($result)" : $result;
    }
}