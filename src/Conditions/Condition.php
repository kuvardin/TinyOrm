<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Conditions;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\Enums\LogicalOperator;
use Kuvardin\TinyOrm\Enums\Operator;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\SpecialValues\IsNull;
use Kuvardin\TinyOrm\SpecialValues\NotNull;
use RuntimeException;

class Condition extends ConditionAbstract
{
    public function __construct(
        public Column $column,
        public IsNull|NotNull|string|float|bool|int|Column $value,
        public ?Operator $operator = null,
        LogicalOperator $prefix = null,
        bool $invert = null,
    )
    {
        parent::__construct($prefix, $invert);
    }

    public static function isNull(
        Column $column,
        LogicalOperator $prefix = null,
        bool $invert = null,
    ): self
    {
        return new self($column, new IsNull, prefix: $prefix, invert: $invert);
    }

    public static function notNull(
        Column $column,
        LogicalOperator $prefix = null,
        bool $invert = null,
    ): self
    {
        return new self($column, new NotNull, prefix: $prefix, invert: $invert);
    }

    public function getQueryString(Parameters $parameters): string
    {
        $operator = $this->operator ?? Operator::Equals;
        switch ($operator) {
            case Operator::Equals:
                if ($this->value instanceof IsNull) {
                    $result = "{$this->column->getFullName()} IS NULL";
                } elseif ($this->value instanceof NotNull) {
                    $result = "{$this->column->getFullName()} IS NOT NULL";
                } elseif ($this->value instanceof Column) {
                    $result = "{$this->column->getFullName()} {$operator->value} {$this->value->getFullName()}";
                } else {
                    $parameter = $parameters->pushValue($this->value);
                    $result = "{$this->column->getFullName()} {$operator->value} :$parameter";
                }
                break;

            case Operator::NotEquals:
                if ($this->value instanceof IsNull) {
                    $result = "{$this->column->getFullName()} IS NOT NULL";
                } elseif ($this->value instanceof NotNull) {
                    $result = "{$this->column->getFullName()} IS NULL";
                } elseif ($this->value instanceof Column) {
                    $result = "{$this->column->getFullName()} {$operator->value} {$this->value->getFullName()}";
                } else {
                    $parameter = $parameters->pushValue($this->value);
                    $result = "{$this->column->getFullName()} {$operator->value} :$parameter";
                }
                break;

            case Operator::Greater:
            case Operator::GreaterOrEqual:
            case Operator::Less:
            case Operator::LessOrEqual:
            case Operator::Like:
            case Operator::ILike:
                if ($this->value instanceof Column) {
                    $result = "{$this->column->getFullName()} {$operator->value} {$this->value->getFullName()}";
                } else {
                    $parameter = $parameters->pushValue($this->value);
                    $result = "{$this->column->getFullName()} {$operator->value} :$parameter";
                }
                break;

            default:
                throw new RuntimeException("Unexpected operator: {$operator->value}");
        }

        return $this->invert ? "NOT ($result)" : $result;
    }
}