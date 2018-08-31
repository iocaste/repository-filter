<?php

namespace Iocaste\Filter;

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
        if (count($this->uuids)) {
            $model = $model->whereIn($repository->getTableName($model) . '.uuid', $this->uuids);
        }

        return $model;
    }
}
