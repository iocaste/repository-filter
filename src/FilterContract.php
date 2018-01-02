<?php

namespace Iocaste\Filter;

/**
 * Interface FilterContract
 */
interface FilterContract
{
    /**
     * @param array $requestAttributes
     *
     * @return mixed
     */
    public function filter(array $requestAttributes);

    /**
     * @return mixed
     */
    public function all();

    /**
     * @param int|null $itemsPerPage
     *
     * @return mixed
     */
    public function paginate(int $itemsPerPage = null);
}
