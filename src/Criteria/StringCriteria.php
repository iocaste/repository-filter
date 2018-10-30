<?php

namespace Iocaste\Filter\Criteria;

use Illuminate\Database\Eloquent\Builder;

class StringCriteria extends AbstractCriteria
{
    /**
     * @param Builder $model
     *
     * @return Builder
     */
    public function apply(Builder $model): Builder
    {
        if ($this->getJsonProperty()) {
            return $this->applyWithJsonProperty($model);
        }

        return $model->where($this->getColumn(), 'LIKE', '%' . mb_strtolower($this->getValue()) . '%');
    }

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

    /**
     * @param Builder $model
     *
     * @return Builder
     */
    protected function applyWithJsonProperty(Builder $model): Builder
    {
        $jsonProperty = '.*.' . $this->normalizeJsonProperty($this->getJsonProperty());
        $search = '%' . mb_strtolower($this->getValue()) . '%';
        $sql = sprintf('JSON_SEARCH(LOWER(%s), \'one\', \'%s\', null,  \'$%s\') is not null', $this->getColumn(), $search, $jsonProperty);

        return $model->whereRaw($sql);
    }

    /**
     * @param $value
     *
     * @return bool|string
     */
    protected function normalizeJsonProperty($value)
    {
        if (strpos($value, '.') === 2) {
            return substr($value, 2);
        }

        return $value;
    }
}
