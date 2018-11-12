<?php

namespace Iocaste\Filter;

use Illuminate\Database\Eloquent\Builder;
use Iocaste\Filter\Exception\FilterBy\TryingToFilterByNonexistentRelation;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;
use Iocaste\Filter\Criteria\CriteriaInterface as FilterCriteriaInterface;

class FilterBy implements CriteriaInterface
{
    use InteractsWithModel;
    use GetsParameterSegments;

    /**
     * @var array
     */
    protected $filter;

    /**
     * @var array
     */
    protected $availableTypes = [
        'like',
        'string',
        'boolean',
        'integer',
        'date',
        'datetime',
    ];

    /**
     * @var string
     */
    protected $defaultType = 'like';

    /**
     * FilterBy constructor.
     *
     * @param $filter
     */
    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param RepositoryInterface $repository
     * @throws TryingToFilterByNonexistentRelation
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if (! empty($this->filter) && \is_array($this->filter)) {
            foreach ($this->filter as $column => $filter) {
                $model = $this->addFilter($model, $column, $filter);
            }
        }

        return $model;
    }

    /**
     * @param $model
     * @param $column
     * @param $filter
     *
     * @throws TryingToFilterByNonexistentRelation
     * @return Builder
     */
    protected function addFilter(Builder $model, string $column, string $filter): Builder
    {
        [$relation, $column, $jsonProperty] = $this->getParameterSegments($column);
        $type = $this->getFilterType($filter);
        $searchValue = $this->getSearchValue($filter);

        if ($relation && ! $this->modelHasRelation($model->getModel(), $relation)) {
            throw new TryingToFilterByNonexistentRelation($relation . ' relation does not exist on model ' . \get_class($model->getModel()));
        }

        $column = $this->getColumn($column);

        if ($relation) {
            $model = $model->whereHas($relation, function ($query) use ($column, $type, $searchValue, $jsonProperty) {
                $this->addFilterByType($query, $column, $type, $searchValue, $jsonProperty);
            });
        } else {
            $model = $this->addFilterByType($model, $column, $type, $searchValue, $jsonProperty);
        }

        return $model;
    }

    /**
     * @param $column
     * @return string
     */
    protected function getColumn($column): string
    {
        if (! $this->hasRelations($column)) {
            return $column;
        }

        return substr($column, strrpos($column, '.') + 1);
    }

    /**
     * @param $filter
     * @return string
     */
    protected function getFilterType(string $filter): string
    {
        if (strpos($filter, ':') === false) {
            return $this->defaultType;
        }

        return substr($filter, 0, strpos($filter, ':'));
    }

    /**
     * @param $filter
     * @return string
     */
    protected function getSearchValue(string $filter): string
    {
        if (strpos($filter, ':') === false) {
            return $filter;
        }

        return substr($filter, strpos($filter, ':') + 1);
    }

    /**
     * @param Builder $model
     * @param string $column
     * @param string $type
     * @param string $value
     * @param null|string $jsonProperty
     *
     * @return Builder
     */
    protected function addFilterByType(
        Builder $model,
        string $column,
        string $type,
        string $value,
        ?string $jsonProperty
    ): Builder
    {
        $criteria = $this->createCriteria($type);
        $criteria->setColumn($column)
            ->setValue($value)
            ->setJsonProperty($jsonProperty);

        return $criteria->apply($model);
    }

    /**
     * @param $column
     * @return bool
     */
    protected function hasRelations($column): bool
    {
        return strpos($column, '.') !== false;
    }

    /**
     * @param string $type
     * @return string
     */
    protected function createCriteria(string $type): FilterCriteriaInterface
    {
        $namespace = 'Iocaste\Filter\Criteria\\';
        $className = $namespace . ucfirst($type) . 'Criteria';

        if (class_exists($className)) {
            return new $className();
        }
        $defaultCriteria = $namespace . 'DefaultCriteria';

        return new $defaultCriteria();
    }
}
