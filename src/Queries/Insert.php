<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\EntityAbstract;
use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Connection;
use Kuvardin\TinyOrm\QueryAbstract;
use Kuvardin\TinyOrm\Table;
use Kuvardin\TinyOrm\Values\ColumnValue;
use Kuvardin\TinyOrm\Values\ValuesSet;
use RuntimeException;

/**
 * @link https://www.postgresql.org/docs/current/sql-insert.html
 */
class Insert extends QueryAbstract
{
    /**
     * @var ValuesSet[]
     */
    protected array $values_sets = [];

    /**
     * @param ValuesSet[] $values_sets
     */
    public function __construct(
        Connection $pdo,
        public Table $into,
        array $values_sets = [],
    )
    {
        parent::__construct($pdo);

        foreach ($values_sets as $values_set) {
            $this->addValuesSet($values_set);
        }
    }

    public function getFinalQuery(Parameters $parameters = null): FinalQuery
    {
        $parameters ??= new Parameters;
        $table_name = $this->into->getFullName() . ($this->into->alias === null ? null : " AS {$this->into->alias}");
        $result = "INSERT INTO $table_name";

        /** @var string[] $column_names */
        $column_names = [];

        $values_rows = [];
        foreach ($this->values_sets as $values_set) {
            $values = [];

            foreach ($values_set->getValues() as $column_value) {
                $column_name = $column_value->column->table?->alias === null
                    ? $column_value->column->name
                    : $column_value->column->getFullName(true);

                if (array_key_exists($column_name, $values)) {
                    throw new RuntimeException("Duplicate column name in values set: $column_name");
                }

                if (!in_array($column_name, $column_names, true)) {
                    $column_names[] = $column_name;
                }

                if ($column_value->value_is_sql) {
                    $values[$column_name] = $column_value->value;
                } elseif (is_bool($column_value->value)) {
                    $values[$column_name] = $column_value->value ? 'True' : 'False';
                } elseif ($column_value->value instanceof EntityAbstract) {
                    $values[$column_name] = $column_value->value->id;
                } else {
                    $values[$column_name] = $parameters->pushValue($column_value->value, $column_value->type);
                }
            }

            $values_rows[] = $values;
        }

        $result .= ' (' . implode(', ', $column_names) . ')';

        $result_values_strings = [];
        foreach ($values_rows as $values_row) {
            $ordered_row = [];

            foreach ($column_names as $column_name) {
                $ordered_row[] = isset($values_row) ? $values_row[$column_name] : 'NULL';
            }

            $result_values_strings[] = '(' . implode(', ', $ordered_row) . ')';
        }

        $result .= ' VALUES ' . implode(', ', $result_values_strings);

        return new FinalQuery($result, $parameters);
    }

    /**
     * @param array<ColumnValue|mixed> $values
     */
    public function addValuesSetFromArray(array $values): self
    {
        $this->addValuesSet(new ValuesSet($this->into, $values));
        return $this;
    }

    public function addValuesSet(ValuesSet $values_set): self
    {
        if ($values_set->getTable()->getFullName() !== $this->into->getFullName()) {
            throw new RuntimeException("Wrong value set table: {$values_set->getTable()->getFullName()}");
        }

        $this->values_sets[] = $values_set;
        return $this;
    }
}