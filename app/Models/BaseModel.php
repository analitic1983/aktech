<?php

namespace App\Models;

use App\Models\Queries\BaseBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \App\Models\Queries\BaseBuilder query()
 * @method \App\Models\Queries\BaseBuilder newQuery()
 */
class BaseModel extends Model
{
    public function newEloquentBuilder($query): Builder
    {
        return new BaseBuilder($query);
    }
}
