<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Values;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\Table;
use RuntimeException;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class ValuesSet
{
    /**
     * @var ColumnValue[]
     */
    protected array $values = [];

    /**
     * @param array<ColumnValue|mixed> $values
     */
    public function __construct(
        readonly public Table $table,
        array $values = [],
    )
    {
        foreach ($values as $key => $value) {
            if (is_int($key)) {
                $this->addColumnValue($value);
            } elseif ($value instanceof ColumnValue) {
                if ($value->column->table !== null && !$value->column->table->isEquals($this->table)) {
                    throw new RuntimeException(
                        "Unexpected column value table: {$value->column->table} (must be {$this->table})",
                    );
                }

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
        ?int $type = null,
        bool $value_is_sql = false,
    ): self
    {
        if (is_string($column)) {
            $column = $this->table->getColumn($column);
        }

        if ($column->table !== null && !$this->table->isEquals($column->table)) {
            throw new RuntimeException("Wrong column table: {$column->table->getFullName()}");
        }

        $this->values[] = new ColumnValue($column, $value, $type, $value_is_sql);
        return $this;
    }

    /**
     * @return ColumnValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function isEmpty(): bool
    {
        return $this->values === [];
    }
}