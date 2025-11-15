<?php

namespace App\Modules\Slots\Dto;

use App\Interfaces\DtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "SlotsAvailabilityDto",
    description: "Список доступности слотов"
)]
class SlotsAvailabilityDto implements DtoInterface
{
    #[OA\Property(
        type: "array",
        items: new OA\Items(ref: SlotAvailabilityDto::class),
    )]
    public array $slots;

    /**
     * @param SlotAvailabilityDto[] $slots
     */
    public function __construct(array $slots)
    {
        $this->slots = $slots;
    }
}
