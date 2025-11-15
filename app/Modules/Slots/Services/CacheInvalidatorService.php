<?php

namespace App\Modules\Slots\Services;

use Illuminate\Support\Facades\Cache;

class CacheInvalidatorService
{
    public function invalidateAvailabilityCache(): void
    {
        $cacheKey = config('slots.availability_cache_key');
        Cache::forget($cacheKey);
    }
}
