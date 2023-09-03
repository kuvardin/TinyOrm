<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Conditions;

use Kuvardin\TinyOrm\Enums\LogicalOperator;
use Kuvardin\TinyOrm\Parameters;
use RuntimeException;

class ConditionsList extends ConditionAbstract
{
    /**
     * @var ConditionAbstract[]
     */
    protected array $items = [];

    /**
     * @param ConditionAbstract[] $items
     */
    public function __construct(
        array $items = [],
        public LogicalOperator $prefix = LogicalOperator::And,
        public bool $invert = false,
    )
    {
        parent::__construct($this->prefix, $this->invert);
        foreach ($items as $item) {
            $this->append($item);
        }
    }

    public function append(ConditionAbstract $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    public function getQueryString(Parameters $parameters): string
    {
        if ($this->items === []) {
            return '1';
        }

        $result_parts = [];
        foreach ($this->items as $item) {
            $item_result = $result_parts === [] ? '' : "{$item->prefix->value} ";
            if ($item instanceof Condition) {
                $item_result .= $item->getQueryString($parameters);
            } elseif ($item instanceof ConditionsList) {
                $item_result .= '(' . $item->getQueryString($parameters) . ')';
            } else {
                throw new RuntimeException('incorrect condition item type: ' . gettype($item));
            }

            $result_parts[] = $item_result;
        }

        $result = implode(' ', $result_parts);
        return $this->invert ? "NOT ($result)" : $result;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}