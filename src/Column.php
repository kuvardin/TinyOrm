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

    public function getFullName(): string
    {
        return $this->table === null ? $this->name : "{$this->table->getFullName()}.{$this->name}";
    }

    public static function validateName(string $name_part): bool
    {
        return (bool)preg_match('|^[a-zA-Z][a-zA-Z0-9_]+$|', $name_part);
    }
}