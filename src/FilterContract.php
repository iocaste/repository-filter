<?php

namespace Iocaste\Filter;

use Illuminate\Http\Request;

/**
 * Interface FilterContract
 */
interface FilterContract
{
    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function filter(Request $request);

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
