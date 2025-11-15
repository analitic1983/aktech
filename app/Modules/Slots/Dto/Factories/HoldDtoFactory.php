<?php

namespace App\Modules\Slots\Dto\Factories;

use App\Modules\Slots\Dto\HoldDto;
use App\Modules\Slots\Models\Hold;

class HoldDtoFactory
{
    public function create(Hold $hold): HoldDto
    {
        return new HoldDto(
            uuid: $hold->uuid,
            slot_uuid: $hold->slot_uuid,
            status: $hold->status->value,
            expires_at: $hold->expires_at,
            confirmed_at: $hold->confirmed_at,
            cancelled_at: $hold->cancelled_at,
            created_at: $hold->created_at,
        );
    }
}
