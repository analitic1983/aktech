<?php

namespace App\Modules\Slots\Checkers;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class IdempotencyChecker
{
    protected const PREFIX = 'idempotency:';
    public const CACHE_DEFAULT_TTL = 30;
    public const LOCK_TTL = 5;
    public const LOCK_WAIT = 10;

    /**
     * @throws LockTimeoutException
     */
    public function lockAndGetPrevious(string $operationName, string $key): array
    {
        $redisKey = self::PREFIX . $operationName . '_' . $key;

        $cached = Cache::get($redisKey);
        if ($cached !== null) {
            return [null, $cached];
        }

        $lock = Cache::lock($redisKey.'-lock', self::LOCK_TTL);
        $lock->block(self::LOCK_WAIT);

        $cached = Cache::get($redisKey);

        return [$lock, $cached];
    }

    public function store(string $operationName, string $key, mixed $value, int $ttl = self::CACHE_DEFAULT_TTL): void
    {
        $redisKey = self::PREFIX . $operationName . '_' . $key;
        Cache::set($redisKey, $value, $ttl);
    }
}
