<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Values;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\Table;
use RuntimeException;

class ValuesSet
{
    /**
     * @var ColumnValue[]
     */
    protected array $values = [];

    public function __construct(
        readonly protected Table $table
    )
    {
    }

    public function add(
        Column|string $column,
        mixed $value,
    ): self
    {
        if (is_string($column)) {
            $column = $this->table->getColumn($column);
        }

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