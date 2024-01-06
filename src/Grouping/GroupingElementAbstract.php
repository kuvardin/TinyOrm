<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Grouping;

use Kuvardin\TinyOrm\Parameters;

abstract class GroupingElementAbstract
{
    abstract public function getQueryString(Parameters $parameters): ?string;
}