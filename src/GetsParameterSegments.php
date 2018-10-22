<?php

namespace Iocaste\Filter;

trait GetsParameterSegments
{
    /**
     * @param $parameter
     *
     * @return array
     */
    protected function getParameterSegments($parameter): array
    {
        $parameter = str_replace(
            'translation_current',
            'translation.' . app('translator')->getLocale(),
            $parameter
        );

        if (strpos($parameter, 'translation') !== false) {
            $result = explode('translation', $parameter);
            $result = array_map(function ($value) {
                return trim($value, '.');
            }, $result);

            return [
                $result[0] ? camel_case($result[0]) : null,
                'translation',
                $result[1],
            ];
        } elseif (strpos($parameter, '.') !== false) {
            $lastDotPosition = strrpos($parameter, '.');

            return [
                camel_case(substr($parameter, 0, $lastDotPosition)),
                substr($parameter, $lastDotPosition + 1),
                null,
            ];
        }

        return [
            null,
            $parameter,
            null,
        ];
    }
}
