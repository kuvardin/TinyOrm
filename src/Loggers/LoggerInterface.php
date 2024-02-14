<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Loggers;

use PDOException;

interface LoggerInterface
{
    public function log(string $query, array $parameters, float $duration, ?PDOException $exception): void;
}