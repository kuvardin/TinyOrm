<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class SqlFilter
{
    private function __construct()
    {

    }

    public static function validateColumnName(string $column_name): bool
    {
        return (bool)preg_match('|^[a-zA-Z0-9\.\-\_]$|', $column_name);
    }
}