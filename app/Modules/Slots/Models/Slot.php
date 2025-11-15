<?php

namespace App\Modules\Slots\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property integer $capacity
 * @property integer $used
 */
class Slot extends BaseModel
{
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class);
    }
}
