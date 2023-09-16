<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Enums;

enum SortDirection: string
{
    case Asc = 'ASC';
    case Desc = 'DESC';
}