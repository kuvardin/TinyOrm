<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\SpecialValues\IsNull;
use Kuvardin\TinyOrm\SpecialValues\NotNull;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class SpecialValues
{
    private static ?IsNull $is_null = null;
    private static ?NotNull $not_null = null;

    private function __construct()
    {

    }

    public static function isNull(): IsNull
    {
        return self::$is_null ??= new IsNull;
    }

    public static function notNull(): NotNull
    {
        return self::$not_null ??= new NotNull;
    }

    public static function nullOrNotNull(?bool $is_null): IsNull|NotNull|null
    {
        if ($is_null !== null) {
            return $is_null ? self::isNull() : self::notNull();
        }

        return null;
    }

    public static function notNullOrNull(?bool $is_not_null): IsNull|NotNull|null
    {
        if ($is_not_null !== null) {
            return $is_not_null ? self::notNull() : self::isNull();
        }

        return null;
    }

    public static function setDefault(): SpecialValues\SetDefault
    {
        return new SpecialValues\SetDefault;
    }
}