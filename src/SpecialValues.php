<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\SpecialValues\IsNull;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class SpecialValues
{
    static ?IsNull $is_null = null;
    static ?IsNull $not_null = null;

    private function __construct()
    {

    }

    public static function isNull(): SpecialValues\IsNull
    {
        return self::$is_null ??= new SpecialValues\IsNull;
    }

    public static function notNull(): SpecialValues\NotNull
    {
        return self::$not_null ??= new SpecialValues\NotNull;
    }

    public static function nullOrNotNull(?bool $is_null): SpecialValues\IsNull|SpecialValues\NotNull|null
    {
        if ($is_null !== null) {
            return $is_null ? self::isNull() : self::notNull();
        }

        return null;
    }

    public static function notNullOrNull(?bool $is_not_null): SpecialValues\IsNull|SpecialValues\NotNull|null
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