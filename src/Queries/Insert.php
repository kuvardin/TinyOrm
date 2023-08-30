<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Queries;

use Kuvardin\TinyOrm\FinalQuery;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\CustomPdo;
use Kuvardin\TinyOrm\QueryAbstract;
use Kuvardin\TinyOrm\Table;
use Kuvardin\TinyOrm\ValuesSet;
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
        CustomPdo $pdo,
        public Table $into,
        array $values_sets = [],
    )
    {
        parent::__construct($pdo);

        foreach ($values_sets as $values_set) {
            $this->addValuesSet($values_set);
        }
    }

    public function getFinalQuery(): FinalQuery
    {
        $parameters = new Parameters;
        $table_name = $this->into->getFullName() . ($this->into->alias === null ? null : "AS {$this->into->alias}");
        $result = "INSERT INTO $table_name";

        $columns_names = [];
        $values_strings = [];

        foreach ($this->values_sets as $values_set) {
            $values = [];

//            foreach ($values_set->add())
        }

        return new FinalQuery($result, $parameters);
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