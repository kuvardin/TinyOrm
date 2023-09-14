<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class FinalQuery
{
    public function __construct(
        public string $value,
        public ?Parameters $parameters = null,
    )
    {

    }
}