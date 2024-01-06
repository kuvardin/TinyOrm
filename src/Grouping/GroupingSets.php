<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Grouping;

use Kuvardin\TinyOrm\Parameters;

class GroupingSets extends GroupingElementAbstract
{
    /**
     * @var GroupingElementAbstract[]
     */
    protected array $grouping_elements = [];

    /**
     * @param GroupingElementAbstract[] $grouping_elements
     */
    public function __construct(array $grouping_elements = [])
    {
        foreach ($grouping_elements as $grouping_element) {
            $this->appendGroupingElement($grouping_element);
        }
    }

    public function appendGroupingElement(GroupingElementAbstract $grouping_element): self
    {
        $this->grouping_elements[] = $grouping_element;
        return $this;
    }

    public function getQueryString(Parameters $parameters): ?string
    {
        $result = [];

        foreach ($this->grouping_elements as $grouping_element) {
            $grouping_element_query = $grouping_element->getQueryString($parameters);
            if ($grouping_element_query !== null) {
                $result[] = $grouping_element_query;
            }
        }

        return $result === [] ? null : 'GROUPING SETS (' . implode(', ', $result) . ')';
    }
}