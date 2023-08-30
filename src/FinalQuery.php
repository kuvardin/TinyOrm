<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

class FinalQuery
{
    public function __construct(
        public string $value,
        public ?Parameters $parameters = null,
    )
    {

    }
}