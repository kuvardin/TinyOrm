<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Traits;

use Kuvardin\TinyOrm\Conditions\ConditionAbstract;
use Kuvardin\TinyOrm\Conditions\ConditionsList;
use Kuvardin\TinyOrm\Enums\LogicalOperator;

trait QueryConditionsListTrait
{
    public ConditionsList $conditions;

    public function setWhere(ConditionAbstract|array|null $condition_item): self
    {
        $this->conditions = is_array($condition_item)
            ? ConditionsList::fromValuesArray($condition_item)
            : new ConditionsList($condition_item === null ? [] : [$condition_item]);

        return $this;
    }

    public function andWhere(ConditionAbstract|array $condition_item): self
    {
        $this->conditions->append(
            is_array($condition_item)
                ? ConditionsList::fromValuesArray($condition_item)
                : new ConditionsList([$condition_item], LogicalOperator::And)
        );

        return $this;
    }

    public function orWhere(ConditionAbstract|array $condition_item): self
    {
        $this->conditions->append(
            is_array($condition_item)
                ? ConditionsList::fromValuesArray($condition_item, LogicalOperator::Or)
                : new ConditionsList([$condition_item], LogicalOperator::Or)
        );

        return $this;
    }
}