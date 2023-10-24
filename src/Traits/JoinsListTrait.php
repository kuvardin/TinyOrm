<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Traits;

use Kuvardin\TinyOrm\Joins\JoinAbstract;

trait JoinsListTrait
{
    /**
     * @var JoinAbstract[]
     */
    protected array $joins = [];

    /**
     * @return JoinAbstract[]
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    public function appendJoin(JoinAbstract $join): self
    {
        $this->joins[] = $join;
        return $this;
    }

    /**
     * @param JoinAbstract[] $joins
     */
    public function setJoins(array $joins): self
    {
        foreach ($joins as $join) {
            $this->appendJoin($join);
        }

        return $this;
    }
}