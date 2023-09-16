<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Enums;

use Kuvardin\TinyOrm\SpecialValues\IsNull;
use Kuvardin\TinyOrm\SpecialValues\NotNull;

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

    public function checkValueValidity(mixed $value): bool
    {
        if (($value instanceof IsNull) || ($value instanceof NotNull)) {
            if ($this !== self::Equals && $this !== self::NotEquals) {
                return false;
            }
        } elseif ($value) {
            // TODO
        }
    }
}