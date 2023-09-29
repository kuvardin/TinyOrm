<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Connection;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Table;
use Kuvardin\TinyOrm\Traits\QueryConditionsListTrait;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 * @link https://www.postgresql.org/docs/current/sql-delete.html
 */
class Delete extends QueryAbstract
{
    use QueryConditionsListTrait;

    public function __construct(
        Connection $connection,
        public Table $table,
        public ?ConditionAbstract $condition_abstract = null,
        public ?string $output_expression = null,
        public bool $only = false,
    )
    {
        parent::__construct($connection);
        $this->setWhere($this->condition_abstract);
    }

    public function setTable(Table $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function setOutputExpression(?string $output_expression): self
    {
        $this->output_expression = $output_expression;
        return $this;
    }

    public function setOnly(bool $only): self
    {
        $this->only = $only;
        return $this;
    }

    public function getFinalQuery(Parameters $parameters = null): FinalQuery
    {
        $parameters ??= new Parameters;
        $result = $this->only
            ? "DELETE FROM ONLY {$this->table->getFullName(true)}"
            : "DELETE FROM {$this->table->getFullName(true)}";

        if (!$this->conditions->isEmpty()) {
            $result .= ' WHERE ' . $this->conditions->getQueryString($parameters);
        }

        if ($this->output_expression !== null && $this->output_expression !== '') {
            $result .= " RETURNING {$this->output_expression}";
        }

        return new FinalQuery($result, $parameters);
    }
}