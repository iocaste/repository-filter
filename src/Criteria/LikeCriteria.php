<?php

namespace Iocaste\Filter\Criteria;

use Illuminate\Database\Eloquent\Builder;

class LikeCriteria extends StringCriteria
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
            return substr($value, 3);
        }

        return $value;
    }
}
