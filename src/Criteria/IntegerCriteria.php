<?php

namespace Iocaste\Filter\Criteria;

class IntegerCriteria extends AbstractCriteria
{
    /**
     * @param $value
     *
     * @return CriteriaInterface
     */
    public function setValue($value): CriteriaInterface
    {
        $this->value = (int) $value;

        return $this;
    }
}
