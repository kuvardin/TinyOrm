<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Joins;

use Kuvardin\TinyOrm\Enums\JoinType;
use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\Parameters;
use Kuvardin\TinyOrm\Table;

class JoinOn extends JoinAbstract
{
    public function __construct(
        JoinType $type,
        Table $table,
        readonly public ExpressionAbstract $expression,
    )
    {
        parent::__construct($type, $table);
    }

    public function getQueryString(Parameters $parameters): string
    {
        return sprintf(
            '%s JOIN %s ON %s',
            $this->type->value,
            $this->table->getFullName(true, true),
            $this->expression->getQueryString($parameters),
        );
    }
}