<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Enums;

enum GroupingMode: string
{
    case All = 'ALL';
    case Distinct = 'DISTINCT';
}