<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Enums;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
enum Operator: string
{
    case Equals = '=';
    case NotEquals = '<>';
    case Greater = '>';
    case GreaterOrEqual = '>=';
    case Less = '<';
    case LessOrEqual = '<=';
    case Like = 'like';
    case ILike = 'ilike';
    case In = 'IN';
    case NotIn = 'NOT IN';
}