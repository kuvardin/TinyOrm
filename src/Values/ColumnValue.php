<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Values;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\EntityAbstract;
use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\Parameters;

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

        if ($this->value instanceof EntityAbstract) {
            return $this->value->id;
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