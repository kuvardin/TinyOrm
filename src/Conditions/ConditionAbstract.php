<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Conditions;

use Kuvardin\TinyOrm\LogicalOperator;
use Kuvardin\TinyOrm\Parameters;

abstract class ConditionAbstract
{
    public LogicalOperator $prefix = LogicalOperator::And;
    public bool $invert = false;

    public function __construct(
        LogicalOperator $prefix = null,
        bool $invert = null,
    )
    {
        $this->prefix = $prefix ?? LogicalOperator::And;
        $this->invert = $invert ?? false;
    }

    abstract public function getQueryString(Parameters $parameters): string;
}