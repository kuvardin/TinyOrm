<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Connection;
use Kuvardin\TinyOrm\QueryAbstract;
use Kuvardin\TinyOrm\Table;

/**
 * @link https://www.postgresql.org/docs/current/sql-select.html
 */
class Select extends QueryAbstract
{
    public function __construct(
        Connection $pdo,
        public ?Table $table = null,
        public ?ConditionAbstract $condition_item = null,
        public ?int $limit = null,
        public ?int $offset = null,
    )
    {
        parent::__construct($pdo);
    }

    public function from(Table $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function where(ConditionAbstract $condition_item): self
    {
        $this->condition_item = $condition_item;
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

    public function getFinalQuery(): FinalQuery
    {
        $parameters = new Parameters;

        $result = "SELECT * FROM \"{$this->table->getFullName()}\"";

        if ($this->condition_item !== null) {
            $result .= ' WHERE ' . $this->condition_item->getQueryString($parameters);
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