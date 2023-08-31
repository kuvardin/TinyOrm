<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Conditions\ConditionAbstract;

class ColumnValue
{
    public function __construct(
        public Column $column,
        public int|float|string|bool|Column|ConditionAbstract|null $value,
    )
    {

    }
}