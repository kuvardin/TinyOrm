<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Conditions;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\Enums\LogicalOperator;
use Kuvardin\TinyOrm\Enums\Operator;
use Kuvardin\TinyOrm\Expressions\BinaryOperation;
use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\Parameters;
use RuntimeException;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
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

    /**
     * Null values will be ignored
     */
    public static function fromValuesArray(
        array $values_array,
        LogicalOperator $prefix = LogicalOperator::And,
        bool $invert = false,
    ): self
    {
        $result = new self(
            prefix: $prefix,
            invert: $invert,
        );

        foreach ($values_array as $column => $value) {
            if ($value === null) {
                continue;
            }

            if ($value instanceof ConditionAbstract) {
                $result->append($value);
            } elseif ($value instanceof ExpressionAbstract) {
                if (is_string($column)) {
                    $result->appendExpression(
                        new BinaryOperation(new Column($column), $value, Operator::Equals->value),
                    );
                } else {
                    $result->appendExpression($value);
                }
            } else {
                $result->append(
                    new Condition(
                        column: $column,
                        value: $value,
                        operator: Operator::Equals,
                        prefix: LogicalOperator::And,
                    ),
                );
            }

        }

        return $result;
    }

    public function append(ConditionAbstract $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    public function appendExpression(
        ExpressionAbstract $expression,
        LogicalOperator $prefix = null,
        bool $invert = null,
    ): self
    {
        $this->append(
            new ConditionExpression(
                expression: $expression,
                prefix: $prefix,
                invert: $invert,
            ),
        );

        return $this;
    }

    public function getQueryString(Parameters $parameters): string
    {
        if ($this->items === []) {
            return 'True';
        }

        $result_parts = [];
        foreach ($this->items as $item) {
            $item_result = $result_parts === [] ? '' : "{$item->prefix->value} ";
            if (($item instanceof Condition) || ($item instanceof ConditionSelect)) {
                $item_result .= $item->getQueryString($parameters);
            } elseif (($item instanceof ConditionsList) || ($item instanceof ConditionExpression)) {
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