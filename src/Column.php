<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use RuntimeException;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class Column extends ExpressionAbstract
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

    public function getQueryString(Parameters $parameters): string
    {
        return $this->getFullName(true);
    }

    public static function validateName(string $name_part): bool
    {
        return $name_part === '*' || preg_match('|^[a-zA-Z][a-zA-Z0-9_]+$|', $name_part);
    }

    public function isEquals(self $another_column): bool
    {
        return $this->name === $another_column->name
            && (
                $this->table === null
                || $another_column->table === null
                || $this->table->isEquals($another_column->table)
            );
    }

    public function getFullName(bool $with_quotes = false): string
    {
        $q = $with_quotes ? '"' : '';

        $name_full = $this->name === '*'
            ? $this->name
            : ($q . $this->name . $q);

        if ($this->table === null) {
            return $name_full;
        }

        if ($this->table->alias !== null) {
            return $this->table->alias . '.' . $name_full;
        }

        return $this->table->getFullName($with_quotes) . '.' . $name_full;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
}