<?php

namespace Iocaste\Filter;

use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;

/**
 * Class LimitBy.
 */
class LimitBy implements CriteriaInterface
{
    /**
     * @var string
     */
    protected $count;

    /**
     * LimitBy constructor.
     *
     * @param $count
     */
    public function __construct($count)
    {
        $this->count = $count;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if ($this->count) {
            return $model->limit($this->count);
        }

        return $model;
    }
}
