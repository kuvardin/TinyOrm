<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Values\ValuesSet;
use RuntimeException;

class Table
{
    public function __construct(
        readonly public string $name,
        readonly public ?string $schema = null,
        readonly public ?string $alias = null,
    )
    {
        if ($this->schema !== null && $this->name === null) {
            throw new RuntimeException("Table name was not set for schema: {$this->schema}");
        }

        if ($this->name === null && $this->schema === null && $this->alias === null) {
            throw new RuntimeException('Empty table data');
        }
    }

    public function getFullName(): string
    {
        return ($this->schema === null ? '' : "{$this->schema}.") . $this->name;
    }

    public function getColumn(string $column_name): Column
    {
        return new Column($column_name, $this);
    }

    public function createValuesSet(): ValuesSet
    {
        return new ValuesSet($this);
    }
}