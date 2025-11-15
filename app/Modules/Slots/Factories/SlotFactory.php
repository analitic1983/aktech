<?php
namespace App\Modules\Slots\Factories;

use App\Modules\Slots\Models\Slot;
use Ramsey\Uuid\Uuid;

readonly class SlotFactory
{
    public function create(int $capacity): Slot
    {
        $slot = new Slot();
        $slot->uuid = Uuid::uuid7();
        $slot->capacity = $capacity;
        $slot->used = 0;
        return $slot;
    }
}
