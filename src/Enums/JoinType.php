<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Enums;

enum JoinType: string
{
    case Inner = 'INNER';
    case Left = 'LEFT';
    case LeftOuter = 'LEFT OUTER';
    case Right = 'RIGHT';
    case RightOuter = 'RIGHT OUTER';
    case Full = 'FULL';
    case FullOuter = 'FULL OUTER';
}