<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

enum LogicalOperator: string
{
    case Or = 'or';
    case And = 'and';
}