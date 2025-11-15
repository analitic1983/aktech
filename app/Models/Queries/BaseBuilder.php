<?php

namespace App\Models\Queries;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BaseBuilder extends Builder
{
    public function findOrBusinessFail(int|string $id, array $columns = ['*']): BaseModel
    {
        try {
            $result = $this->findOrFail($id, $columns);
        } catch (ModelNotFoundException $modelNotFoundException) {
            $modelClass = $modelNotFoundException->getModel();
            throw new \App\Exceptions\ModelNotFoundBusinessException($modelClass, $id);
        }

        return $result;
    }
}
