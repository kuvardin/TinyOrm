<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm\Sorting;

use Kuvardin\TinyOrm\Parameters;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class SortingSettings
{
    /**
     * @var Sorting[]
     */
    protected array $sorting_list = [];

    public function __construct(array $sorting_list)
    {
        $this->setSortingList($sorting_list);
    }

    /**
     * @return Sorting[]
     */
    public function getSortingList(): array
    {
        return $this->sorting_list;
    }

    public function setSortingList(array $sorting_list): self
    {
        $this->sorting_list = [];

        foreach ($sorting_list as $sorting) {
            $this->appendSorting($sorting);
        }

        return $this;
    }

    public function appendSorting(Sorting $sorting): self
    {
        $this->sorting_list[] = $sorting;
        return $this;
    }
    
    public function getQueryString(Parameters $parameters): string
    {
        return implode(
            ', ',
            array_map(
                static fn(Sorting $sorting) => $sorting->getQueryString($parameters),
                $this->sorting_list,
            ),
        );
    }

    public function isEmpty(): bool
    {
        return $this->sorting_list === [];
    }
}