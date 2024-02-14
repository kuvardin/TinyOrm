<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Loggers;

use DateTime;
use PDOException;
use RuntimeException;

class FileLogger implements LoggerInterface
{
    public function __construct(
        readonly public string $file_path,
    )
    {

    }

    public function log(string $query, array $parameters, float $duration, ?PDOException $exception): void
    {
        $f = fopen($this->file_path, 'a');

        if ($f === false) {
            throw new RuntimeException("File {$this->file_path} opening failed");
        }

        $current_date = new DateTime();
        $string = sprintf(
            '[%s | %.2f ms.] %s',
            $current_date->format('Y.m.d H:i:s:u'),
            $duration,
            $query,
        );

        if ($parameters !== []) {
            $string .= "\n\t" . print_r($parameters, true);
        }

        if ($exception !== null) {
            $string .= "\n\tError #{$exception->getCode()}: {$exception->getMessage()}";
        }

        $string .= "\n\n";

        if (fwrite($f, $string) === false) {
            throw new RuntimeException("Writing fo file {$this->file_path} failed");
        }

        if (!fclose($f)) {
            throw new RuntimeException("File {$this->file_path} closing failed");
        }
    }
}