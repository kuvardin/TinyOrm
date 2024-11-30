<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use RuntimeException;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class SelectExpression
{
    private function __construct(
        readonly public ?ExpressionAbstract $expression,
        readonly public ?string $output_name = null,
    )
    {
        if ($this->output_name !== null && !self::validateOutputName($this->output_name)) {
            throw new RuntimeException("Incorrect select expression output name: {$this->output_name}");
        }
    }

    public static function allColumns(): self
    {
        return new self(null);
    }

    public static function expression(ExpressionAbstract $expression, ?string $output_name = null): self
    {
        return new self($expression, $output_name);
    }

    public static function validateOutputName(string $output_name): bool
    {
        return (bool)preg_match('|^[a-zA-Z0-9\-_]+$|', $output_name);
    }

    public function getQueryString(Parameters $parameters): string
    {
        if ($this->expression === null) {
            return '*';
        }

        $result = $this->expression->getQueryString($parameters);

        if ($this->output_name !== null) {
            $result .= ' AS "' . $this->output_name . '"';
        }

        return $result;
    }
}