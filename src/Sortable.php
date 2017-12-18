<?php

namespace Iocaste\Filter;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Trait Sortable
 */
trait Sortable
{
    /**
     * Builds array from order-by string that has order direction and order-by key.
     *
     * @param $orderByString
     * @return array
     */
    protected function getOrder($orderByString): array
    {
        $values = explode(',', $orderByString);

        return [
            'by' => $values[0],
            'direction' => isset($values[1]) ? strtoupper($values[1]) : 'DESC',
        ];
    }

    /**
     * Builds paginator from existing collection.
     *
     * @param Collection $items
     * @return LengthAwarePaginator
     */
    protected function buildPaginator($items): LengthAwarePaginator
    {
        $perPage = app('request')->get('per_page', 24);
        $page = app('request')->get('page', 1);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page
        );
    }

    /**
     * Returns array of query param keys.
     *
     * @param array $queryFilters
     *
     * @return array
     */
    public function getQueryParams(array $queryFilters): array
    {
        return array_map(function ($param) {
            return $param['queryParameter'];
        }, $queryFilters);
    }
}
