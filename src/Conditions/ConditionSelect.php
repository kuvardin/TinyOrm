<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Conditions;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\Enums\LogicalOperator;
use Kuvardin\TinyOrm\Enums\Operator;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Queries\Select;

class ConditionSelect extends ConditionAbstract
{
    public Column|string $column;

    public function __construct(
        Column|string $column,
        public Select $select,
        public Operator $operator = Operator::In,
        LogicalOperator $prefix = null,
        bool $invert = null,
    )
    {
        $this->column = is_string($column) ? new Column($column) : $column;
        parent::__construct($prefix, $invert);
    }

    public function getQueryString(Parameters $parameters): string
    {
        $column_name = $this->column->getFullName(true);
        $sub_query = $this->select->getQueryString($parameters);
        $result = "$column_name {$this->operator->value} ($sub_query)";
        return $this->invert ? "NOT ($result)" : $result;
    }
}