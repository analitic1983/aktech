<?php

namespace App\Services;

use App\Exceptions\HoldMismatchException;
use App\Exceptions\HoldNotConfirmableException;
use App\Exceptions\SlotUnavailableException;
use App\Models\Hold;
use App\Models\Slot;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SlotService
{
    private const AVAILABILITY_CACHE_KEY = 'slots:availability';
    private const AVAILABILITY_LOCK_KEY = 'slots:availability:lock';

    /**
     * Return slots availability, cached for a short random interval.
     *
     * @return Collection<int, array<string, int>>
     */
    public function getAvailability(): Collection
    {
        $cacheKey = self::AVAILABILITY_CACHE_KEY;

        $cached = Cache::get($cacheKey);
        if ($cached instanceof Collection) {
            return $cached;
        }

        try {
            return Cache::lock(self::AVAILABILITY_LOCK_KEY, 5)->block(5, function () use ($cacheKey) {
                $cached = Cache::get($cacheKey);
                if ($cached instanceof Collection) {
                    return $cached;
                }

                $fresh = $this->buildAvailabilitySnapshot();

                Cache::put($cacheKey, $fresh, now()->addSeconds(random_int(5, 15)));

                return $fresh;
            });
        } catch (LockTimeoutException $exception) {
            return $this->buildAvailabilitySnapshot();
        }
    }

    public function invalidateAvailabilityCache(): void
    {
        Cache::forget(self::AVAILABILITY_CACHE_KEY);
    }

    /**
     * @return array{hold: Hold, created: bool}
     */
    public function createHold(Slot $slot, string $idempotencyKey): array
    {
        $existing = Hold::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing instanceof Hold) {
            if ($existing->slot_id !== $slot->id) {
                throw new HoldMismatchException('Idempotency key already used for another slot.');
            }

            return ['hold' => $existing, 'created' => false];
        }

        $created = false;

        $hold = DB::transaction(function () use ($slot, $idempotencyKey, &$created) {
            /** @var Slot $lockedSlot */
            $lockedSlot = Slot::query()->lockForUpdate()->findOrFail($slot->id);

            $activeHoldCount = Hold::query()
                ->active()
                ->where('slot_id', $lockedSlot->id)
                ->count();

            if (($lockedSlot->remaining - $activeHoldCount) <= 0) {
                throw new SlotUnavailableException('Slot capacity has been exhausted.');
            }

            $created = true;

            return Hold::query()->create([
                'slot_id' => $lockedSlot->id,
                'status' => Hold::STATUS_HELD,
                'idempotency_key' => $idempotencyKey,
                'expires_at' => CarbonImmutable::now()->addMinutes(5),
            ]);
        });

        if ($created) {
            $this->invalidateAvailabilityCache();
        }

        return ['hold' => $hold, 'created' => true];
    }

    public function confirmHold(Hold $hold): Hold
    {
        $shouldInvalidate = false;

        $confirmed = DB::transaction(function () use ($hold, &$shouldInvalidate) {
            /** @var Hold $lockedHold */
            $lockedHold = Hold::query()->lockForUpdate()->findOrFail($hold->id);

            if (! $lockedHold->canConfirm()) {
                throw new HoldNotConfirmableException('Hold is not in a confirmable state.');
            }

            /** @var Slot $slot */
            $slot = Slot::query()->lockForUpdate()->findOrFail($lockedHold->slot_id);

            if ($slot->remaining <= 0) {
                throw new SlotUnavailableException('Slot capacity has been exhausted.');
            }

            $slot->decrement('remaining');

            $lockedHold->fill([
                'status' => Hold::STATUS_CONFIRMED,
                'confirmed_at' => CarbonImmutable::now(),
            ]);
            $lockedHold->save();

            $shouldInvalidate = true;

            return $lockedHold->fresh();
        });

        if ($shouldInvalidate) {
            $this->invalidateAvailabilityCache();
        }

        return $confirmed;
    }

    public function cancelHold(Hold $hold): Hold
    {
        $shouldInvalidate = false;

        $cancelled = DB::transaction(function () use ($hold, &$shouldInvalidate) {
            /** @var Hold $lockedHold */
            $lockedHold = Hold::query()->lockForUpdate()->findOrFail($hold->id);

            if ($lockedHold->status === Hold::STATUS_CANCELLED) {
                return $lockedHold;
            }

            $shouldInvalidate = true;

            /** @var Slot $slot */
            $slot = Slot::query()->lockForUpdate()->findOrFail($lockedHold->slot_id);

            if ($lockedHold->status === Hold::STATUS_CONFIRMED) {
                $slot->update([
                    'remaining' => min($slot->capacity, $slot->remaining + 1),
                ]);
            }

            $lockedHold->fill([
                'status' => Hold::STATUS_CANCELLED,
                'cancelled_at' => CarbonImmutable::now(),
            ]);
            $lockedHold->save();

            return $lockedHold->fresh();
        });

        if ($shouldInvalidate) {
            $this->invalidateAvailabilityCache();
        }

        return $cancelled;
    }

    private function buildAvailabilitySnapshot(): Collection
    {
        $activeHoldCounts = Hold::query()
            ->active()
            ->selectRaw('slot_id, COUNT(*) as aggregate')
            ->groupBy('slot_id')
            ->pluck('aggregate', 'slot_id');

        return Slot::query()
            ->orderBy('id')
            ->get(['id', 'capacity', 'remaining'])
            ->map(function (Slot $slot) use ($activeHoldCounts) {
                $held = (int) $activeHoldCounts->get($slot->id, 0);

                return [
                    'slot_id' => $slot->id,
                    'capacity' => $slot->capacity,
                    'remaining' => max(0, $slot->remaining - $held),
                ];
            });
    }
}
