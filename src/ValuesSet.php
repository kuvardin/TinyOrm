<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use RuntimeException;

class ValuesSet
{
    /**
     * @var ColumnValue[]
     */
    protected array $values = [];

    public function __construct(
        protected Table $table
    )
    {
    }

    public function add(Column $column, int|float|string|bool|Column|ConditionAbstract|null $value): self
    {
        if ($column->table !== null && $column->table->getFullName() !== $this->table->getFullName()) {
            throw new RuntimeException("Wrong column table: {$column->table->getFullName()}");
        }

        $this->values[] = new ColumnValue($column, $value);
        return $this;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * @return ColumnValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}