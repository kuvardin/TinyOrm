<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Traits;

use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\Conditions\ConditionsList;
use Kuvardin\TinyOrm\Enums\LogicalOperator;

trait QueryConditionsListTrait
{
    public ConditionsList $conditions;

    public function setWhere(?ConditionAbstract $condition_item): self
    {
        $this->conditions = new ConditionsList($condition_item === null ? [] : [$condition_item]);
        return $this;
    }

    public function andWhere(ConditionAbstract $condition_item): self
    {
        $this->conditions->append(new ConditionsList([$condition_item], LogicalOperator::And));
        return $this;
    }

    public function orWhere(ConditionAbstract $condition_item): self
    {
        $this->conditions->append(new ConditionsList([$condition_item], LogicalOperator::Or));
        return $this;
    }
}