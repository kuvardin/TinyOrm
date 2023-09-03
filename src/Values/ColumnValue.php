<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Values;

use Kuvardin\TinyOrm\Column;

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
}