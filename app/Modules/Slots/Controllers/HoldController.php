<?php

namespace App\Modules\Slots\Controllers;

use App\Exceptions\HoldNotConfirmableException;
use App\Exceptions\SlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Modules\Slots\Dto\Factories\HoldDtoFactory;
use App\Modules\Slots\Dto\HoldDto;
use App\Modules\Slots\Models\Hold;
use App\Modules\Slots\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\SerializerInterface;

class HoldController extends Controller
{
    public function __construct(
        private readonly SlotService $slotService,
        private readonly HoldDtoFactory $holdDtoFactory,
        SerializerInterface $serializer
    ) {
        parent::__construct($serializer);
    }

    /**
     * @throws SlotUnavailableException
     * @throws \Throwable
     */
    #[OA\Post(
        path: "/api/slots/{slotUuid}/hold",
        summary: "Создать hold для слота",
        tags: ["Holds"],
        parameters: [
            new OA\Parameter(
                name: "slotUuid",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid")
            ),
            new OA\Parameter(
                name: "Idempotency-Key",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешный ответ",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessFlag"),
                        new OA\Schema(ref: HoldDto::class),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Некорректные данные запроса",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 409,
                description: "Бизнес-ошибка (например, слот недоступен)",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function hold(Request $request, string $slotUuid): JsonResponse
    {
        $hold = $this->slotService->createHold($slotUuid);

        return $this->jsonSuccessHold($hold);
    }

    /**
     * @throws SlotUnavailableException
     * @throws \Throwable
     * @throws HoldNotConfirmableException
     */
    #[OA\Post(
        path: "/api/holds/{holdUuid}/confirm",
        summary: "Подтвердить hold",
        tags: ["Holds"],
        parameters: [
            new OA\Parameter(
                name: "holdUuid",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid")
            ),
            new OA\Parameter(
                name: "Idempotency-Key",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешное подтверждение",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessFlag"),
                        new OA\Schema(ref: HoldDto::class),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Некорректный Idempotency-Key",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 409,
                description: "Невозможно подтвердить hold или слот недоступен",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function confirm(Request $request, string $holdUuid): JsonResponse
    {
        $hold = $this->slotService->confirmHold($holdUuid);

        return $this->jsonSuccessHold($hold);
    }

    /**
     * @throws \Throwable
     */
    #[OA\Post(
        path: "/api/holds/{holdUuid}/cancel",
        summary: "Отменить hold",
        tags: ["Holds"],
        parameters: [
            new OA\Parameter(
                name: "holdUuid",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid")
            ),
            new OA\Parameter(
                name: "Idempotency-Key",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешная отмена",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessFlag"),
                        new OA\Schema(ref: HoldDto::class),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Некорректный Idempotency-Key",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 409,
                description: "Невозможно отменить hold",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function cancel(Request $request, string $holdUuid): JsonResponse
    {
        $hold = $this->slotService->cancelHold($holdUuid);

        return $this->jsonSuccessHold($hold);
    }

    private function jsonSuccessHold(Hold $hold): JsonResponse
    {
        return $this->jsonSuccess($this->holdDtoFactory->create($hold));
    }
}
