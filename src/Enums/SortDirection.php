<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Enums;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
enum SortDirection: string
{
    case Asc = 'ASC';
    case Desc = 'DESC';
}