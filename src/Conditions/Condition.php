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

class Condition extends ConditionAbstract
{
    public Column|string $column;

    public function __construct(
        Column|string $column,
        public mixed $value,
        public Operator $operator = Operator::Equals,
        LogicalOperator $prefix = null,
        bool $invert = null,
        public ?int $pdo_param_type = null,
    )
    {
        $this->column = is_string($column) ? new Column($column) : $column;
        parent::__construct($prefix, $invert);
    }

    public static function isNull(
        Column|string $column,
        LogicalOperator $prefix = null,
        bool $invert = null,
    ): self
    {
        return new self($column, new IsNull, prefix: $prefix, invert: $invert);
    }

    public static function notNull(
        Column|string $column,
        LogicalOperator $prefix = null,
        bool $invert = null,
    ): self
    {
        return new self($column, new NotNull, prefix: $prefix, invert: $invert);
    }

    public function getQueryString(Parameters $parameters): string
    {
        $value = $this->value instanceof EntityAbstract ? $this->value->id : $this->value;

        $result = null;

        if ($this->operator === Operator::Equals) {
            if ($value instanceof IsNull) {
                $result = "{$this->column->getFullName()} IS NULL";
            } elseif ($value instanceof NotNull) {
                $result = "{$this->column->getFullName()} IS NOT NULL";
            }
        } elseif ($this->operator === Operator::NotEquals) {
            if ($value instanceof IsNull) {
                $result = "{$this->column->getFullName()} IS NOT NULL";
            } elseif ($value instanceof NotNull) {
                $result = "{$this->column->getFullName()} IS NULL";
            }
        }

        if ($result === null) {
            if ($value instanceof Column) {
                $result = "{$this->column->getFullName()} {$this->operator->value} {$value->getFullName()}";
            } else {
                $parameter = $parameters->pushValue($value, $this->pdo_param_type);
                $result = "{$this->column->getFullName()} {$this->operator->value} $parameter";
            }
        }

        return $this->invert ? "NOT ($result)" : $result;
    }
}