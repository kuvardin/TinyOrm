<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

class SpecialValues
{
    private function __construct()
    {

    }

    public static function isNull(): SpecialValues\IsNull
    {
        return new SpecialValues\IsNull;
    }

    public static function notNull(): SpecialValues\NotNull
    {
        return new SpecialValues\NotNull;
    }

    public static function setDefault(): SpecialValues\SetDefault
    {
        return new SpecialValues\SetDefault;
    }
}