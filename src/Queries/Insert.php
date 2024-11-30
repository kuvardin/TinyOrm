<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Connection;
use Kuvardin\TinyOrm\Table;
use Kuvardin\TinyOrm\Values\ColumnValue;
use Kuvardin\TinyOrm\Values\ValuesSet;
use RuntimeException;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
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
        Connection $connection,
        public Table $table,
        array $values_sets = [],
        public ?string $output_expression = null,
    )
    {
        parent::__construct($connection);

        foreach ($values_sets as $values_set) {
            $this->addValuesSet($values_set);
        }
    }

    public function setOutputExpression(?string $output_expression): self
    {
        $this->output_expression = $output_expression;
        return $this;
    }

    public function getFinalQuery(?Parameters $parameters = null): FinalQuery
    {
        $parameters ??= new Parameters;
        $result = "INSERT INTO {$this->table->getFullName(true)}";

        /** @var string[] $column_names */
        $column_names = [];

        $values_rows = [];
        foreach ($this->values_sets as $values_set) {
            $values = [];

            foreach ($values_set->getValues() as $column_value) {
                if (array_key_exists($column_value->column->name, $values)) {
                    throw new RuntimeException("Duplicate column name in values set: {$column_value->column->name}");
                }

                if (!in_array($column_value->column->name, $column_names, true)) {
                    $column_names[] = $column_value->column->name;
                }

                $values[$column_value->column->name] = $column_value->getValueSql($parameters);
            }

            $values_rows[] = $values;
        }

        $result .= ' ("' . implode('", "', $column_names) . '")';

        $result_values_strings = [];
        foreach ($values_rows as $values_row) {
            $ordered_row = [];

            foreach ($column_names as $column_name) {
                $ordered_row[] = array_key_exists($column_name, $values_row) ? $values_row[$column_name] : 'DEFAULT';
            }

            $result_values_strings[] = '(' . implode(', ', $ordered_row) . ')';
        }

        $result .= ' VALUES ' . implode(', ', $result_values_strings);

        if ($this->output_expression !== null && $this->output_expression !== '') {
            $result .= " RETURNING {$this->output_expression}";
        }

        return new FinalQuery($result, $parameters);
    }

    /**
     * @param array<ColumnValue|mixed> $values
     */
    public function addValuesSetFromArray(array $values): self
    {
        $this->addValuesSet(new ValuesSet($this->table, $values));
        return $this;
    }

    public function addValuesSet(ValuesSet $values_set): self
    {
        if (!$this->table->isEquals($values_set->table)) {
            throw new RuntimeException("Wrong value set table: {$values_set->table->getFullName()}");
        }

        $this->values_sets[] = $values_set;
        return $this;
    }
}