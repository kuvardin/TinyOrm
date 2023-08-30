<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Values\IsNull;
use Kuvardin\TinyOrm\Values\NotNull;

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

        }
    }
}