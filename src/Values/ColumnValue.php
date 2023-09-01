<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Values;

use Kuvardin\TinyOrm\Column;

class ColumnValue
{
    public function __construct(
        public Column $column,
        public mixed $value,
    )
    {

    }
}