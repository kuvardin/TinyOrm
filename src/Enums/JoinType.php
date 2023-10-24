<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Enums;

enum JoinType: string
{
    case Inner = 'INNER';
    case LeftOuter = 'LEFT OUTER';
    case RightOuter = 'RIGHT OUTER';
    case FullOuter = 'FULL OUTER';
}