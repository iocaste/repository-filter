<?php

namespace Iocaste\Filter;

use Illuminate\Database\Eloquent\Model;

trait InteractsWithModel
{
    /**
     * @param Model $model
     * @param string $relation
     * @return bool
     */
    public function modelHasRelation(Model $model, string $relation): bool
    {
        if ($model->relationLoaded($relation)) {
            return true;
        }

        if (method_exists($model, $relation)) {
            return true;
        }

        return false;
    }
}
