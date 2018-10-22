<?php

namespace Iocaste\Filter;

use Illuminate\Database\Eloquent\Relations;
use Iocaste\Filter\Exception\OrderBy\OrderByParameterContainsIllegalSymbols;
use Iocaste\Filter\Exception\OrderBy\TryingToJoinUnavailableMethod;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;
use ReflectionMethod;

/**
 * Class OrderBy.
 */
class OrderBy implements CriteriaInterface
{
    use GetsParameterSegments;

    /**
     * @var string
     */
    protected $orderBy;

    /**
     * @var string
     */
    protected $sortBy;

    /**
     * @var string
     */
    protected $orderByPattern = '/^[a-z0-9\.\_]+$/i';

    /**
     * @var array
     */
    protected $relationsTypesAllowedForJoin = [
        Relations\BelongsTo::class,
        Relations\HasOne::class,
    ];

    /**
     * OrderBy constructor.
     *
     * @param $count
     * @param mixed $orderBy
     */
    public function __construct($orderBy)
    {
        $explode = explode(',', $orderBy);

        $this->orderBy = $explode[0];
        $this->sortBy = $explode[1] ?? 'asc';
        if (! \in_array($this->sortBy, ['asc', 'desc'])) {
            $this->sortBy = 'asc';
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param RepositoryInterface $repository
     *
     * @throws OrderByParameterContainsIllegalSymbols
     * @throws TryingToJoinUnavailableMethod
     * @throws \ReflectionException
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if (!empty($this->orderBy)) {
            if (!preg_match($this->orderByPattern, $this->orderBy)) {
                throw new OrderByParameterContainsIllegalSymbols();
            }

            [$relation, $column, $jsonProperty] = $this->getParameterSegments($this->orderBy);

            if ($relation) {
                $model = $this->joinRelationship($model, $relation);
            }

            $columnWithTableAlias = $relation
                ? str_replace('.', '_', $relation) . '.' . $column
                : $column;

            if ($jsonProperty) {
                $model = $model->orderBy(
                    app('db')->raw('JSON_EXTRACT('
                        . $columnWithTableAlias . ', '
                        . "'$." . $jsonProperty . "'"
                        . ')'),
                    $this->sortBy
                );
            } else {
                $model = $model->orderBy($columnWithTableAlias, $this->sortBy);
            }
        }

        return $model;
    }

    /**
     * @param $query
     * @param $relationshipName
     *
     * @throws TryingToJoinUnavailableMethod
     * @throws \ReflectionException
     *
     * @return
     */
    protected function joinRelationship($query, $relationshipName)
    {
        $usedSegments = [];
        $model = $query->getModel();

        foreach (explode('.', $relationshipName) as $relationshipNameSegment) {
            $relationMethodReflection = new ReflectionMethod(
                get_class($model),
                $relationshipNameSegment
            );

            if (!in_array($relationMethodReflection->getReturnType(), $this->relationsTypesAllowedForJoin)) {
                throw new TryingToJoinUnavailableMethod();
            }

            $relationship = $model->{$relationshipNameSegment}();

            $currentAlias = !empty($usedSegments)
                ? implode('_', $usedSegments)
                : $relationship->getParent()->getTable();

            $alias = !empty($usedSegments)
                ? implode('_', $usedSegments) . '_' . $relationshipNameSegment
                : $relationshipNameSegment;

            if ($relationship instanceof Relations\HasOneOrMany) {
                $joinTableColumn = $relationship->getForeignKeyName();
                $mainTableColumn = 'id';
            } else {
                $joinTableColumn = $relationship->getOwnerKey();
                $mainTableColumn = $relationship->getForeignKey();
            }

            $joinedTables = array_map(function ($join) {
                return $join->table;
            }, $query->getQuery()->joins ?: []);

            if (array_search($relationship->getRelated()->getTable() . ' as ' . $alias, $joinedTables) === false) {
                $query = $query->leftJoin(
                    $relationship->getRelated()->getTable() . ' as ' . $alias,
                    $alias . '.' . $joinTableColumn,
                    '=',
                    $currentAlias . '.' . $mainTableColumn
                );
            }

            $model = $relationship->getRelated();
            $usedSegments[] = $relationshipNameSegment;
        }

        return $query;
    }
}
