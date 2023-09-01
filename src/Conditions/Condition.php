<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Conditions;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\EntityAbstract;
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
        public IsNull|NotNull|string|float|bool|int|Column|EntityAbstract $value,
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
        $value = $this->value instanceof EntityAbstract ? $this->value->id : $this->value;
        $result = null;

        if ($operator === Operator::Equals) {
            if ($value instanceof IsNull) {
                $result = "{$this->column->getFullName()} IS NULL";
            } elseif ($value instanceof NotNull) {
                $result = "{$this->column->getFullName()} IS NOT NULL";
            }
        } elseif ($operator === Operator::NotEquals) {
            if ($value instanceof IsNull) {
                $result = "{$this->column->getFullName()} IS NOT NULL";
            } elseif ($value instanceof NotNull) {
                $result = "{$this->column->getFullName()} IS NULL";
            }
        }

        if ($result === null) {
            if ($value instanceof Column) {
                $result = "{$this->column->getFullName()} {$operator->value} {$value->getFullName()}";
            } else {
                $parameter = $parameters->pushValue($value);
                $result = "{$this->column->getFullName()} {$operator->value} $parameter";
            }
        }

        return $this->invert ? "NOT ($result)" : $result;
    }
}