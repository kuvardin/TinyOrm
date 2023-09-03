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

    /**
     * @param array<ColumnValue|string> $values
     */
    public function __construct(
        readonly protected Table $table,
        array $values = [],
    )
    {
        foreach ($values as $key => $value) {
            if (is_int($key)) {
                $this->addColumnValue($value);
            } else {
                $this->add($this->table->getColumn($key), $value);
            }
        }
    }

    public function addColumnValue(ColumnValue $column_value): self
    {
        $this->values[] = $column_value;
        return $this;
    }

    public function add(
        Column|string $column,
        mixed $value,
        int $type = null,
        bool $value_is_sql = false,
    ): self
    {
        if (is_string($column)) {
            $column = $this->table->getColumn($column);
        }

        if ($column->table !== null && $column->table->getFullName() !== $this->table->getFullName()) {
            throw new RuntimeException("Wrong column table: {$column->table->getFullName()}");
        }

        $this->values[] = new ColumnValue($column, $value, $type, $value_is_sql);
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