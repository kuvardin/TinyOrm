<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use RuntimeException;

class Column
{
    public function __construct(
        readonly public string $name,
        readonly public ?Table $table = null,
    )
    {
        if (!self::validateName($this->name)) {
            throw new RuntimeException("Incorrect column name: $this->name");
        }
    }

    public function getFullName(bool $use_table_alias = false): string
    {
        if ($this->table !== null) {
            return $use_table_alias && $this->table->alias !== null
                ? "{$this->table->alias}.{$this->name}"
                : "{$this->table->getFullName()}.{$this->name}";
        }

        return $this->name;
    }

    public static function validateName(string $name_part): bool
    {
        return (bool)preg_match('|^[a-zA-Z][a-zA-Z0-9_]+$|', $name_part);
    }
}