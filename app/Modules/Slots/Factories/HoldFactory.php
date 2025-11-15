<?php

namespace App\Modules\Slots\Factories;

use App\Modules\Slots\Enums\HoldStatusEnum;
use App\Modules\Slots\Models\Hold;
use Carbon\CarbonImmutable;
use Ramsey\Uuid\Uuid;

class HoldFactory
{
    public function create(string $slotUuid, HoldStatusEnum $status, CarbonImmutable $expiresAt): Hold
    {
        $hold = new Hold();
        $hold->uuid = Uuid::uuid7();
        $hold->slot_uuid = $slotUuid;
        $hold->status = $status;
        $hold->created_at = CarbonImmutable::now();
        $hold->expires_at = $expiresAt;

        return $hold;
    }
}
