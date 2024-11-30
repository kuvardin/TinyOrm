<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\Connection;
use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\SelectExpression;
use Kuvardin\TinyOrm\Table;
use Kuvardin\TinyOrm\Traits\QueryConditionsListTrait;
use Kuvardin\TinyOrm\Values\ColumnValue;
use Kuvardin\TinyOrm\Values\ValuesSet;
use RuntimeException;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 * @link https://www.postgresql.org/docs/current/sql-update.html
 */
class Update extends QueryAbstract
{
    use QueryConditionsListTrait;

    public ValuesSet $values_set;

    /**
     * @var SelectExpression[]
     */
    protected array $output_expressions = [];

    public function __construct(
        Connection $connection,
        public Table $table,
        ?ValuesSet $values_set = null,
        ?ConditionAbstract $condition_item = null,
        array $output_expressions = [],
        public ?bool $only = false,
    )
    {
        parent::__construct($connection);

        if ($output_expressions !== []) {
            $this->setOutputExpressions($output_expressions);
        }

        $this->setWhere($condition_item);
        $this->setValuesSet($values_set);
    }

    public function setTable(Table $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return SelectExpression[]
     */
    public function getOutputExpressions(): array
    {
        return $this->output_expressions;
    }

    public function setOutputExpressions(array $output_expressions): self
    {
        $this->output_expressions = [];

        foreach ($output_expressions as $output_expression) {
            $this->appendOutputExpression($output_expression);
        }

        return $this;
    }

    public function appendOutputExpression(SelectExpression $output_expression): self
    {
        $this->output_expressions[] = $output_expression;
        return $this;
    }

    /**
     * @param array<ColumnValue|mixed> $values
     */
    public function setValues(array $values): self
    {
        $this->values_set = new ValuesSet($this->table, $values);
        return $this;
    }

    public function setValuesSet(?ValuesSet $values_set = null): self
    {
        if ($values_set === null) {
            $this->values_set = new ValuesSet($this->table);
        } elseif (!$this->table->isEquals($values_set->table)) {
            throw new RuntimeException("Wrong values set table: {$values_set->table->getFullName()}");
        } else {
            $this->values_set = $values_set;
        }

        return $this;
    }

    public function setOnly(bool $only): self
    {
        $this->only = $only;
        return $this;
    }

    public function getFinalQuery(?Parameters $parameters = null): FinalQuery
    {
        if ($this->values_set->isEmpty()) {
            throw new RuntimeException('Values set is empty');
        }

        $parameters ??= new Parameters;
        $result = $this->only
            ? "UPDATE ONLY {$this->table->getFullName(true)}"
            : "UPDATE {$this->table->getFullName(true)}";

        $set_strings = [];
        foreach ($this->values_set->getValues() as $column_value) {
            $set_strings[] = sprintf(
                '"%s" = %s',
                $column_value->column->name,
                $column_value->getValueSql($parameters),
            );
        }

        $result .= ' SET ' . implode(', ', $set_strings);

        if (!$this->conditions->isEmpty()) {
            $result .= ' WHERE ' . $this->conditions->getQueryString($parameters);
        }

        if ($this->output_expressions !== []) {
            $output_expressions_query = implode(
                ', ',
                array_map(
                    static fn(SelectExpression $se) => $se->getQueryString($parameters),
                    $this->output_expressions,
                ),
            );

            $result .= " RETURNING $output_expressions_query";
        }

        return new FinalQuery($result, $parameters);
    }


}