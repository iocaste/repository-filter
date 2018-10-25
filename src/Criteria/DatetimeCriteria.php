<?php

namespace Iocaste\Filter\Criteria;

use Illuminate\Support\Carbon;

class DatetimeCriteria extends AbstractCriteria
{
    /**
     * @param $value
     *
     * @return CriteriaInterface
     */
    public function setValue($value): CriteriaInterface
    {
        $this->value = Carbon::parse($value)->toDateString();

        return $this;
    }
}
