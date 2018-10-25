<?php

namespace Iocaste\Filter\Criteria;

use Illuminate\Database\Eloquent\Builder;

interface CriteriaInterface
{
    /**
     * @param $column
     * @return CriteriaInterface
     */
    public function setColumn($column): CriteriaInterface;

    /**
     * @param $value
     *
     * @return CriteriaInterface
     */
    public function setValue($value): CriteriaInterface;

    /**
     * @param $jsonProperty
     *
     * @return CriteriaInterface
     */
    public function setJsonProperty($jsonProperty): CriteriaInterface;

    /**
     * @return mixed
     */
    public function getColumn();

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return mixed
     */
    public function getJsonProperty();

    /**
     * @param $model
     *
     * @return Builder
     */
    public function apply(Builder $model): Builder;
}
