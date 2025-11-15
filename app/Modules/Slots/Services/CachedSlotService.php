<?php

namespace App\Modules\Slots\Services;

use App\Modules\Slots\Dto\SlotsAvailabilityDto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

readonly class CachedSlotService
{
    public function __construct(private SlotService $slotService)
    {
    }

    private const AVAILABILITY_LOCK_KEY = 'slots:availability:lock';

    private const CACHE_LOCK_SECONDS = 15;

    private const MIN_CACHE_STORE_SECONDS = 5;
    private const MAX_CACHE_STORE_SECONDS = 15;

    /**
     * Return slots availability, cached for a short random interval.
     */
    public function getAvailability(): SlotsAvailabilityDto
    {
        $cacheKey = config('slots.availability_cache_key');

        $cached = Cache::get($cacheKey);
        if ($cached !== null ) {
            return $cached;
        }

        return Cache::lock(self::AVAILABILITY_LOCK_KEY, self::CACHE_LOCK_SECONDS)->block(self::CACHE_LOCK_SECONDS, function () use ($cacheKey) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            $slots = $this->slotService->getAvailability();

            Cache::put($cacheKey, $slots, now()->addSeconds(random_int(self::MIN_CACHE_STORE_SECONDS, self::MAX_CACHE_STORE_SECONDS)));

            return $slots;
        });
    }
}
