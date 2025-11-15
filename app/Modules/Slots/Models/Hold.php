<?php

namespace App\Modules\Slots\Models;

use App\Models\BaseModel;
use App\Modules\Slots\Enums\HoldStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Hold - hold one slot item
 *
 * @property string $uuid
 * @property string $slot_uuid
 * @property HoldStatusEnum $status
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $confirmed_at
 * @property CarbonImmutable|null $cancelled_at
 */
class Hold extends BaseModel
{
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $casts = [
        'status' => HoldStatusEnum::class,
        'expires_at' => 'immutable_datetime',
        'confirmed_at' => 'immutable_datetime',
        'cancelled_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
    ];

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', HoldStatusEnum::HELD->value)
            ->where(function (Builder $builder) {
                $builder
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', CarbonImmutable::now());
            });
    }

    public function isExpired(): bool
    {
        return $this->isHeld()
            && ($this->expires_at instanceof CarbonImmutable)
            && $this->expires_at->isPast();
    }

    public function canConfirm(): bool
    {
        return $this->status === HoldStatusEnum::HELD && !$this->isExpired();
    }

    public function confirm(): void
    {
        $this->status = HoldStatusEnum::CONFIRMED;
        $this->confirmed_at = CarbonImmutable::now();
    }

    public function cancel(): void
    {
        $this->status = HoldStatusEnum::CANCELLED;
        $this->cancelled_at = CarbonImmutable::now();
    }

    public function isHeld(): bool
    {
        return $this->status == HoldStatusEnum::HELD;
    }

    public function isConfirmed(): bool
    {
        return $this->status == HoldStatusEnum::CONFIRMED;
    }

    public function isCanceled(): bool
    {
        return $this->status == HoldStatusEnum::CANCELLED;
    }
}
