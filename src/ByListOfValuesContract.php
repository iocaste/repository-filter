<?php

namespace Iocaste\Filter;

use Iocaste\Microservice\Foundation\Repository\SqlRepository;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

abstract class ByListOfValuesContract implements CriteriaInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Comma separated value or value
     * ByCommaValueContract constructor.
     * @param $value
     */
    final public function __construct($value)
    {
        $this->value = explode(',', $value);
    }

    /**
     * Returns field name in table
     * @return mixed
     */
    abstract protected function getField(): string;

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->value;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param RepositoryInterface $repository
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $column = $repository->getColumnName($this->getField(), $model);

        return $model->whereIn($column, $this->getValues());
    }
}
