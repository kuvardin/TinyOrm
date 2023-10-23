<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Conditions;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\EntityAbstract;
use Kuvardin\TinyOrm\Enums\LogicalOperator;
use Kuvardin\TinyOrm\Enums\Operator;
use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\SpecialValues\IsNull;
use Kuvardin\TinyOrm\SpecialValues\NotNull;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
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

    public static function nullOrNotNull(
        Column|string $column,
        bool|null $is_null,
        LogicalOperator $prefix = null,
        bool $invert = null,
    ): ?self
    {
        if ($is_null !== null) {
            return new self($column, $is_null ? new IsNull : new NotNull(), prefix: $prefix, invert: $invert);
        }

        return null;
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
        $value = $this->value instanceof EntityAbstract ? $this->value->getId() : $this->value;

        $result = null;
        $column_name = $this->column->getFullName(true);

        if ($this->operator === Operator::Equals) {
            if ($value instanceof IsNull) {
                $result = "{$column_name} IS NULL";
            } elseif ($value instanceof NotNull) {
                $result = "{$column_name} IS NOT NULL";
            }
        } elseif ($this->operator === Operator::NotEquals) {
            if ($value instanceof IsNull) {
                $result = "{$column_name} IS NOT NULL";
            } elseif ($value instanceof NotNull) {
                $result = "{$column_name} IS NULL";
            }
        }

        if ($result === null) {
            if ($value instanceof Column) {
                $result = "{$column_name} {$this->operator->value} {$value->getFullName(true)}";
            } elseif ($value instanceof ExpressionAbstract) {
                $result = "{$column_name} {$this->operator->value} {$value->getQueryString($parameters)}";
            } else {
                $parameter = $parameters->pushValue($value, $this->pdo_param_type);
                $result = "{$column_name} {$this->operator->value} $parameter";
            }
        }

        return $this->invert ? "NOT ($result)" : $result;
    }
}