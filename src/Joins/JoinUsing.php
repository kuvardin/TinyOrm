<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Joins;

use Kuvardin\TinyOrm\Column;
use Kuvardin\TinyOrm\Enums\JoinType;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Table;
use RuntimeException;

class JoinUsing extends JoinAbstract
{
    readonly public array $column_names;

    public function __construct(
        JoinType $type,
        Table $table,
        array $columns,
    )
    {
        parent::__construct($type, $table);

        if ($columns === []) {
            throw new RuntimeException('Empty USING JOIN columns list');
        }

        $columns_names = [];
        foreach ($columns as $column) {
            if ($column instanceof Column) {
                $columns_names[] = $column->name;
            } elseif (is_string($column)) {
                $columns_names[] = $column;
            } else {
                throw new RuntimeException(
                    'Incorrect column type: ' . is_object($column) ? $column::class : gettype($column),
                );
            }
        }

        $this->column_names = $columns_names;
    }

    public function getQueryString(Parameters $parameters): string
    {
        return sprintf(
            '%s JOIN %s USING ("%s")',
            $this->type->value,
            $this->table->getFullName(true, true),
            implode('", "', $this->column_names),
        );
    }
}