<?php

namespace App\Modules\Slots\Dto;

use App\Interfaces\DtoInterface;
use Carbon\CarbonImmutable;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "HoldDto",
    description: "Данные о hold"
)]
class HoldDto implements DtoInterface
{
    #[OA\Property(
        type: "string",
        format: "uuid"
    )]
    public string $uuid;

    #[OA\Property(
        type: "string",
        format: "uuid"
    )]
    public string $slot_uuid;

    #[OA\Property(
        description: "Статус hold: active / confirmed / cancelled / expired",
        type: "string"
    )]
    public string $status;

    #[OA\Property(
        description: "Когда hold автоматически истечёт",
        type: "string",
        format: "date-time",
        nullable: true
    )]
    public ?string $expires_at;

    #[OA\Property(
        description: "Когда hold был подтверждён",
        type: "string",
        format: "date-time",
        nullable: true
    )]
    public ?string $confirmed_at;

    #[OA\Property(
        description: "Когда hold был отменён",
        type: "string",
        format: "date-time",
        nullable: true
    )]
    public ?string $cancelled_at;

    #[OA\Property(
        type: "string",
        format: "date-time"
    )]
    public string $created_at;

    public function __construct(
        string $uuid,
        string $slot_uuid,
        string $status,
        ?CarbonImmutable $expires_at,
        ?CarbonImmutable $confirmed_at,
        ?CarbonImmutable $cancelled_at,
        CarbonImmutable $created_at,
    ) {
        $this->uuid         = $uuid;
        $this->slot_uuid    = $slot_uuid;
        $this->status       = $status;
        $this->expires_at   = optional($expires_at)->toIso8601String();
        $this->confirmed_at = optional($confirmed_at)->toIso8601String();
        $this->cancelled_at = optional($cancelled_at)->toIso8601String();
        $this->created_at   = $created_at->toIso8601String();
    }
}
