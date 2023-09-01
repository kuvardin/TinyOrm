<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Values;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\EntityAbstract;

class ColumnValue
{
    public function __construct(
        public Column $column,
        public int|float|string|bool|Column|ConditionAbstract|EntityAbstract|null $value,
    )
    {

    }
}