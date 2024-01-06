<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Grouping;

use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\Parameters;

class GroupingRollup extends GroupingElementAbstract
{
    /**
     * @var ExpressionAbstract[]
     */
    protected array $expressions = [];

    /**
     * @param ExpressionAbstract[] $expressions
     */
    public function __construct(array $expressions = [])
    {
        foreach ($expressions as $expression) {
            $this->appendExpression($expression);
        }
    }

    public function appendExpression(ExpressionAbstract $expression): self
    {
        $this->expressions[] = $expression;
        return $this;
    }

    public function getQueryString(Parameters $parameters): ?string
    {
        $result = [];

        foreach ($this->expressions as $expression) {
            $result[] = $expression->getQueryString($parameters);
        }

        return $result === [] ? null : 'ROLLUP (' . implode(', ', $result) . ')';
    }
}