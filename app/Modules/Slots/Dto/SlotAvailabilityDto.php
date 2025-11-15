<?php

namespace App\Modules\Slots\Dto;

use App\Interfaces\DtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "SlotAvailabilityDto",
    description: "Информация о доступности слота"
)]
class SlotAvailabilityDto implements DtoInterface
{
    #[OA\Property(
        type: "string",
        format: "uuid"
    )]
    public string $slot_uuid;

    #[OA\Property(
        type: "integer"
    )]
    public int $capacity;

    #[OA\Property(
        type: "integer",
        description: "Количество доступных мест с учётом активных hold"
    )]
    public int $remaining;

    public function __construct(string $slot_uuid, int $capacity, int $remaining)
    {
        $this->slot_uuid = $slot_uuid;
        $this->capacity = $capacity;
        $this->remaining = $remaining;
    }
}
