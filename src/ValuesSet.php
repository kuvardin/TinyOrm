<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use RuntimeException;

class ValuesSet
{
    /**
     * @var Column[]
     */
    protected array $columns = [];

    /**
     * @var array<int,int|float|bool|string|Column|null|ConditionAbstract>
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
            throw new RuntimeException();
        }

        $this->columns[] = $column;
        $this->values[] = $value;
        return $this;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getValues(): array
    {

    }
}