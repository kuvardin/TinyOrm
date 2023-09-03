<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\Conditions\ConditionsList;
use Kuvardin\TinyOrm\Enums\LogicalOperator;
use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Connection;
use Kuvardin\TinyOrm\QueryAbstract;
use Kuvardin\TinyOrm\Table;
use Kuvardin\TinyOrm\Traits\QueryConditionsListTrait;

/**
 * @link https://www.postgresql.org/docs/current/sql-select.html
 */
class Select extends QueryAbstract
{
    use QueryConditionsListTrait;

    public function __construct(
        Connection $connection,
        public ?Table $table = null,
        ?ConditionAbstract $condition_item = null,
        public ?int $limit = null,
        public ?int $offset = null,
    )
    {
        parent::__construct($connection);
        $this->where($condition_item);
    }

    public function from(Table $table): self
    {
        $this->table = $table;
        return $this;
    }


    public function limit(?int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(?int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function getFinalQuery(Parameters $parameters = null): FinalQuery
    {
        $parameters ??= new Parameters;

        $result = "SELECT * FROM \"{$this->table->getFullName()}\"";

        if (!$this->conditions->isEmpty()) {
            $result .= ' WHERE ' . $this->conditions->getQueryString($parameters);
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