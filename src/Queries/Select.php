<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\Conditions\ConditionsList;
use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\CustomPdo;
use Kuvardin\TinyOrm\QueryAbstract;
use Kuvardin\TinyOrm\Table;

/**
 * @link https://www.postgresql.org/docs/current/sql-select.html
 */
class Select extends QueryAbstract
{
    public function __construct(
        CustomPdo $pdo,
        public ?Table $table = null,
        public ?ConditionsList $conditions = null,
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

        if ($this->conditions !== null) {
            $result .= 'WHERE ' . $this->conditions->getQueryString($parameters);
        }

        if ($this->limit !== null) {
            $result .= 'LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $result .= 'OFFSET ' . $this->offset;
        }

        return new FinalQuery($result, $parameters);
    }
}