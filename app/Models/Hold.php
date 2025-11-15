<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hold extends Model
{
    use HasFactory;

    public const STATUS_HELD = 'held';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'slot_id',
        'status',
        'idempotency_key',
        'expires_at',
        'confirmed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'expires_at' => 'immutable_datetime',
        'confirmed_at' => 'immutable_datetime',
        'cancelled_at' => 'immutable_datetime',
    ];

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_HELD)
            ->where(function (Builder $builder) {
                $builder
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', CarbonImmutable::now());
            });
    }

    public function isExpired(): bool
    {
        return $this->expires_at instanceof CarbonImmutable && $this->expires_at->isPast();
    }

    public function canConfirm(): bool
    {
        return $this->status === self::STATUS_HELD && ! $this->isExpired();
    }
}
