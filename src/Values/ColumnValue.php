<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Values;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\EntityAbstract;
use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\Parameters;
use RuntimeException;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class ColumnValue
{
    readonly public Column $column;

    public function __construct(
        Column|string $column,
        readonly public mixed $value,
        readonly public ?int $type = null,
        readonly public bool $value_is_sql = false,
    )
    {
        if ($this->value instanceof self) {
            throw new RuntimeException('Column value cannot be instance of ColumnValue');
        }

        $this->column = is_string($column) ? new Column($column) : $column;
    }

    public function getValueSql(Parameters $parameters): string
    {
        if ($this->value_is_sql) {
            return $this->value;
        }

        if (is_bool($this->value)) {
            return $this->value ? 'True' : 'False';
        }

        if (is_array($this->value)) {
            return json_encode($this->value, JSON_THROW_ON_ERROR);
        }

        if ($this->value instanceof EntityAbstract) {
            return (string)$this->value->getId();
        }

        if ($this->value instanceof ExpressionAbstract) {
            return '(' . $this->value->getQueryString($parameters) . ')';
        }

        if ($this->value === null) {
            return 'NULL';
        }

        return $parameters->pushValue($this->value, $this->type);
    }
}