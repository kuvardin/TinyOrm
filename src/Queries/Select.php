<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\Expressions\ExpressionSql;
use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Connection;
use Kuvardin\TinyOrm\SelectExpression;
use Kuvardin\TinyOrm\Sorting\SortingSettings;
use Kuvardin\TinyOrm\Table;
use Kuvardin\TinyOrm\Traits\QueryConditionsListTrait;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 * @link https://www.postgresql.org/docs/current/sql-select.html
 */
class Select extends QueryAbstract
{
    use QueryConditionsListTrait;

    public function __construct(
        Connection $connection,
        public ?Table $table = null,
        protected array $select_expressions = [],
        ?ConditionAbstract $condition_item = null,
        public ?SortingSettings $sorting_settings = null,
        public ?int $limit = null,
        public ?int $offset = null,
    )
    {
        parent::__construct($connection);
        $this->setWhere($condition_item);
    }

    public function setTable(Table $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return SelectExpression[]
     */
    public function getSelectExpressions(): array
    {
        return $this->select_expressions;
    }

    public function setSelectExpressions(array $select_expressions): self
    {
        $this->select_expressions = [];

        foreach ($select_expressions as $select_expression) {
            $this->appendSelectExpression($select_expression);
        }

        return $this;
    }

    public function appendSelectExpression(SelectExpression $select_expression): self
    {
        $this->select_expressions[] = $select_expression;
        return $this;
    }

    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset(?int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function setSortingSettings(?SortingSettings $sorting_settings): self
    {
        $this->sorting_settings = $sorting_settings;
        return $this;
    }

    public function count(): int
    {
        $select = new self(
            connection: $this->connection,
            table: $this->table,
            select_expressions: [
                SelectExpression::expression(new ExpressionSql('COUNT(*)')),
            ],
            condition_item: $this->conditions,
        );

        $result = $select->execute()->fetchColumn();
        return $result === false ? 0 : $result;
    }

    public function getFinalQuery(Parameters $parameters = null): FinalQuery
    {
        $parameters ??= new Parameters;

        if ($this->select_expressions === []) {
            $select_expressions_query = '*';
        } else {
            $select_expressions_query = implode(
                ', ',
                array_map(
                    static fn(SelectExpression $se) => $se->getQueryString($parameters),
                    $this->select_expressions,
                ),
            );
        }

        $result = "SELECT $select_expressions_query";

        $result .= " FROM {$this->table->getFullName(true)}";

        if (!$this->conditions->isEmpty()) {
            $result .= ' WHERE ' . $this->conditions->getQueryString($parameters);
        }

        if ($this->sorting_settings !== null && !$this->sorting_settings->isEmpty()) {
            $result .= ' ORDER BY ' . $this->sorting_settings->getQueryString($parameters);
        }

        if ($this->limit !== null) {
            $result .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $result .= ' OFFSET ' . $this->offset;
        }

        return new FinalQuery($result, $parameters);
    }
}