<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Sorting;

use Kuvardin\TinyOrm\Enums\SortDirection;
use Kuvardin\TinyOrm\Expressions\ExpressionAbstract;
use Kuvardin\TinyOrm\Parameters;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class Sorting
{
    public function __construct(
        readonly public ExpressionAbstract $expression,
        readonly public SortDirection $direction,
        readonly ?bool $nulls_first = null,
    )
    {

    }

    public static function asc(ExpressionAbstract $expression, bool $nulls_first = null): self
    {
        return new self($expression, SortDirection::Asc, $nulls_first);
    }

    public static function desc(ExpressionAbstract $expression, bool $nulls_first = null): self
    {
        return new self($expression, SortDirection::Desc, $nulls_first);
    }

    public function getQueryString(Parameters $parameters): string
    {
        $result = $this->expression->getQueryString($parameters) . ' ' . $this->direction->value;
        if ($this->nulls_first !== null) {
            $result .= ' NULLS ' . ($this->nulls_first ? 'FIRST' : 'LAST');
        }

        return $result;
    }
}