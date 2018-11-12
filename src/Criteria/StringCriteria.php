<?php

namespace Iocaste\Filter\Criteria;

use Illuminate\Database\Eloquent\Builder;

class StringCriteria extends AbstractCriteria
{
    /**
     * @param $value
     *
     * @return CriteriaInterface
     */
    public function setValue($value): CriteriaInterface
    {
        $this->value = filter_var(filter_var($value, FILTER_SANITIZE_STRING), FILTER_SANITIZE_MAGIC_QUOTES);

        return $this;
    }
}
