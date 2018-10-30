<?php

namespace Iocaste\Filter\Criteria;

class BooleanCriteria extends AbstractCriteria
{
    /**
     * @param $value
     *
     * @return CriteriaInterface
     */
    public function setValue($value): CriteriaInterface
    {
        $this->value = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        return $this;
    }
}
