<?php

namespace Iocaste\Filter\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class AbstractCriteria
 */
abstract class AbstractCriteria implements CriteriaInterface
{
    /**
     * @var
     */
    protected $column;

    /**
     * @var
     */
    protected $value;

    /**
     * @var
     */
    protected $jsonProperty;

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getJsonProperty()
    {
        return $this->jsonProperty;
    }

    /**
     * @param $column
     *
     * @return CriteriaInterface
     */
    public function setColumn($column): CriteriaInterface
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @param $jsonProperty
     *
     * @return CriteriaInterface
     */
    public function setJsonProperty($jsonProperty): CriteriaInterface
    {
        $this->jsonProperty = $jsonProperty;

        return $this;
    }

    /**
     * @param Builder $model
     * @return Builder
     */
    public function apply(Builder $model): Builder
    {
        return $model->where($this->getColumn(), $this->getValue());
    }
}
