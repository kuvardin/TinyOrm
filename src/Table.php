<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Values\ValuesSet;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class Table
{
    public function __construct(
        readonly public string $name,
        readonly public ?string $schema = null,
    )
    {

    }

    public function isEquals(self $another_table): bool
    {
        return $this->name === $another_table->name
            && ($this->schema === null || $another_table->schema === null || $this->schema === $another_table->schema);
    }
    
    public function getFullName(bool $with_quotes = false): string
    {
        $q = $with_quotes ? '"' : '';
        return ($this->schema === null ? '' : "$q{$this->schema}$q.") . "$q{$this->name}$q";
    }

    public function getColumn(string $column_name): Column
    {
        return new Column($column_name, $this);
    }

    public function createValuesSet(): ValuesSet
    {
        return new ValuesSet($this);
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
}