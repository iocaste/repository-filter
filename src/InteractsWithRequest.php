<?php

namespace Iocaste\Filter;

use Illuminate\Support\Arr;

/**
 * Trait InteractsWithRequest
 */
trait InteractsWithRequest
{
    /**
     * Get a subset containing the provided keys with values from the input data.
     *
     * @param $keys
     * @param array $input
     *
     * @return array
     */
    public function only($keys, array $input = []): array
    {
        $keys = \is_array($keys) ? $keys : \func_get_args();

        $results = [];

        foreach ($keys as $key) {
            Arr::set($results, $key, data_get($input, $key));
        }

        return $results;
    }

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param $keys
     * @param array $input
     *
     * @return mixed
     */
    public function except($keys, array $input = [])
    {
        $keys = \is_array($keys) ? $keys : \func_get_args();

        Arr::forget($input, $keys);

        return $input;
    }

    /**
     * Determine if the input array contains a given input item key.
     *
     * @param $key
     * @param array $input
     *
     * @return bool
     */
    public function exists($key, array $input = []): bool
    {
        $keys = \is_array($key) ? $key : \func_get_args();

        foreach ($keys as $value) {
            if (! Arr::has($input, $value)) {
                return false;
            }
        }

        return true;
    }
}
