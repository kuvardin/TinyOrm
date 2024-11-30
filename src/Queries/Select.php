<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\Enums\GroupingMode;
use Kuvardin\TinyOrm\Expressions\ExpressionSql;
use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Grouping\GroupingElementAbstract;
use Kuvardin\TinyOrm\Joins\JoinAbstract;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Connection;
use Kuvardin\TinyOrm\SelectExpression;
use Kuvardin\TinyOrm\Sorting\SortingSettings;
use Kuvardin\TinyOrm\Table;
use Kuvardin\TinyOrm\Traits\JoinsListTrait;
use Kuvardin\TinyOrm\Traits\QueryConditionsListTrait;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 * @link https://www.postgresql.org/docs/current/sql-select.html
 */
class Select extends QueryAbstract
{
    use QueryConditionsListTrait;
    use JoinsListTrait;

    /**
     * @var SelectExpression[]
     */
    protected array $select_expressions = [];

    /**
     * @var GroupingElementAbstract[]
     */
    protected array $grouping_elements = [];

    /**
     * @param SelectExpression[] $select_expressions
     * @param JoinAbstract[] $joins
     * @param GroupingElementAbstract[] $grouping_elements
     */
    public function __construct(
        Connection $connection,
        public ?Table $table = null,
        array $select_expressions = [],
        array $joins = [],
        ?ConditionAbstract $condition_item = null,
        public ?SortingSettings $sorting_settings = null,
        array $grouping_elements = [],
        public ?GroupingMode $grouping_mode = null,
        public ?int $limit = null,
        public ?int $offset = null,
    )
    {
        parent::__construct($connection);
        $this->setWhere($condition_item);

        if ($select_expressions !== []) {
            $this->setSelectExpressions($select_expressions);
        }

        if ($joins !== []) {
            $this->setJoins($joins);
        }

        if ($grouping_elements !== []) {
            $this->setGroupingElements($grouping_elements);
        }
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

    public function setGroupingMode(?GroupingMode $grouping_mode): self
    {
        $this->grouping_mode = $grouping_mode;
        return $this;
    }

    public function setGroupingElements(array $grouping_elements): self
    {
        $this->grouping_elements = [];

        foreach ($grouping_elements as $grouping_element) {
            $this->appendGroupingElement($grouping_element);
        }

        return $this;
    }

    public function appendGroupingElement(GroupingElementAbstract $grouping_element): self
    {
        $this->grouping_elements[] = $grouping_element;
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

    public function getFinalQuery(?Parameters $parameters = null): FinalQuery
    {
        $parameters ??= new Parameters;

        $select_expressions = $this->select_expressions === []
            ? [SelectExpression::allColumns()]
            : $this->select_expressions;

        $select_expressions_query = implode(
            ', ',
            array_map(
                static fn(SelectExpression $se) => $se->getQueryString($parameters),
                $select_expressions,
            ),
        );

        $result = "SELECT $select_expressions_query";

        $result .= " FROM {$this->table->getFullName(true, true)}";

        if ($this->joins !== []) {
            $result .= implode(
                ' ',
                array_map(static fn(JoinAbstract $join) => $join->getQueryString($parameters), $this->joins),
            );
        }

        if (!$this->conditions->isEmpty()) {
            $result .= ' WHERE ' . $this->conditions->getQueryString($parameters);
        }

        if ($this->grouping_elements !== []) {
            $grouping_elements_queries = [];

            foreach ($this->grouping_elements as $grouping_element) {
                $grouping_elements_query = $grouping_element->getQueryString($parameters);

                if ($grouping_elements_query !== null) {
                    $grouping_elements_queries[] = $grouping_elements_query;
                }
            }

            if ($grouping_elements_queries !== []) {
                $result .= 'GROUP BY ';

                if ($this->grouping_mode !== null) {
                    $result .= $this->grouping_mode->value . ' ';
                }

                $result .= implode(', ', $grouping_elements_queries);
            }
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