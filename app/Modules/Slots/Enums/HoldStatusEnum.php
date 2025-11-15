<?php

namespace App\Modules\Slots\Enums;

enum HoldStatusEnum: string
{
    case HELD = 'held';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
}
