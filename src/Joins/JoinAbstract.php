<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Joins;

use Kuvardin\TinyOrm\Enums\JoinType;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Table;

abstract class JoinAbstract
{
    public function __construct(
        readonly public JoinType $type,
        readonly public Table $table,
    )
    {

    }

    abstract public function getQueryString(Parameters $parameters): string;
}