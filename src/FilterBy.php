<?php

namespace Iocaste\Filter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Iocaste\Filter\Exception\FilterBy\TryingToFilterByNonexistentRelation;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

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
        'string',
        'boolean',
        'integer',
        'date',
        'datetime',
    ];

    /**
     * @var string
     */
    protected $defaultType = 'string';

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
     * @return Builder
     */
    protected function addFilterByType(Builder $model, string $column, string $type, string $value, ?string $jsonProperty): Builder
    {
        if ($jsonProperty) {
            $column = app('db')->raw('LOWER(' . $column . "->'$." . $jsonProperty . "')");
        }

        switch ($type) {
            case 'string':
                $model = $model->where($column, 'LIKE', '%' . mb_strtolower($value) . '%');
                break;
            case 'boolean':
                $model = $model->where($column, $this->prepareValue($value, $type));
                break;
            case 'integer':
                $model = $model->where($column, $this->prepareValue($value, $type));
                break;
            case 'date':
                $model = $model->whereDate($column, $this->prepareValue($value, $type));
                break;
            case 'datetime':
                $model = $model->where($column, $this->prepareValue($value, $type));
                break;
        }

        return $model;
    }

    /**
     * @param $value
     * @param string $type
     * @return mixed
     */
    protected function prepareValue($value, string $type)
    {
        switch ($type) {
            case 'string':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                break;
            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'integer':
                $value = (int) $value;
                break;
            case 'date':
                $value = Carbon::parse($value)->toDateString();
                break;
            case 'datetime':
                $value = Carbon::parse($value)->toDateTimeString();
                break;
        }

        return $value;
    }

    /**
     * @param $column
     * @return bool
     */
    protected function hasRelations($column): bool
    {
        return strpos($column, '.') !== false;
    }
}
