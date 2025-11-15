<?php

namespace App\Modules\Slots\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Slots\Dto\SlotsAvailabilityDto;
use App\Modules\Slots\Services\CachedSlotService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\SerializerInterface;

class SlotsController extends Controller
{
    public function __construct(
        private readonly CachedSlotService $cachedSlotService,
        SerializerInterface $serializer
    ) {
        parent::__construct($serializer);
    }

    #[OA\Get(
        path: "/api/slots/availability",
        description: "Возвращает список всех слотов с capacity и remaining. В случае успешного ответа — success=true и данные слотов, в случае ошибки — success=false и сообщение.",
        summary: "Получить доступность всех слотов",
        tags: ["Slots"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешный ответ",
                content: new OA\JsonContent(
                    allOf: [
                        // success=true из общей схемы
                        new OA\Schema(ref: "#/components/schemas/SuccessFlag"),
                        new OA\Schema(ref: SlotsAvailabilityDto::class),
                    ]
                )
            ),
            new OA\Response(
                response: 408,
                description: "Таймаут блокировки, попробуйте запрос позже",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
    public function availability(): JsonResponse
    {
        $slotsAvailability = $this->cachedSlotService->getAvailability();

        return $this->jsonSuccess($slotsAvailability);
    }
}
