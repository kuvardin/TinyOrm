<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Exception;

use PDOException;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class AlreadyExists extends PDOException
{

}