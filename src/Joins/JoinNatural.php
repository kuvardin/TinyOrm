<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Joins;

use Kuvardin\TinyOrm\Enums\JoinType;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Table;

class JoinNatural extends JoinAbstract
{
    public function __construct(JoinType $type, Table $table)
    {
        parent::__construct($type, $table);
    }

    public function getQueryString(Parameters $parameters): string
    {
        return sprintf(
            'NATURAL %s JOIN %s',
            $this->type->value,
            $this->table->getFullName(true, true),
        );
    }
}