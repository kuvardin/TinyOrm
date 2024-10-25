<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Loggers;

use DateTime;
use PDOException;
use RuntimeException;

/**
 * Logging the results of query execution to a file
 */
class FileLogger implements LoggerInterface
{
    /**
     * @param string $file_path Path to the file where logging will be performed
     * @param int|null $execution_duration_min The minimum amount of time to execute a request in milliseconds
     */
    public function __construct(
        readonly public string $file_path,
        readonly public ?int $execution_duration_min = null,
    )
    {

    }

    public function log(string $query, array $parameters, float $duration, ?PDOException $exception): void
    {
        if (
            $this->execution_duration_min !== null
            && $exception === null
            && $duration < $this->execution_duration_min
        ) {
            return;
        }

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