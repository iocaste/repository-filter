<?php

namespace Iocaste\Filter;

use Iocaste\Microservice\Foundation\Repository\SqlRepository;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;

/**
 * Class ByListOfUuids.
 */
class ByListOfUuids implements CriteriaInterface
{
    /**
     * @var string
     */
    protected $uuids;

    /**
     * ByListOfUuids constructor.
     *
     * @param string $uuids
     */
    public function __construct(string $uuids)
    {
        $this->uuids = explode(',', $uuids);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param RepositoryInterface $repository
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $column = $repository instanceof SqlRepository
            ? $repository->getTableName($model) . '.uuid'
            : $this->getField();

        if (count($this->uuids)) {
            $model = $model->whereIn($column, $this->uuids);
        }

        return $model;
    }
}
