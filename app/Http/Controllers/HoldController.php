<?php

namespace App\Http\Controllers;

use App\Exceptions\HoldMismatchException;
use App\Exceptions\HoldNotConfirmableException;
use App\Exceptions\SlotUnavailableException;
use App\Models\Hold;
use App\Models\Slot;
use App\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HoldController extends Controller
{
    public function __construct(private readonly SlotService $slotService)
    {
    }

    public function store(Request $request, Slot $slot): JsonResponse
    {
        $idempotencyKey = (string) $request->header('Idempotency-Key', '');
        if (! Str::isUuid($idempotencyKey)) {
            return response()->json([
                'message' => 'Idempotency-Key header must be a valid UUID.',
            ], 422);
        }

        try {
            $result = $this->slotService->createHold($slot, $idempotencyKey);
        } catch (SlotUnavailableException|HoldMismatchException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 409);
        }

        $status = $result['created'] ? 201 : 200;

        return response()->json($this->formatHold($result['hold']), $status);
    }

    public function confirm(Hold $hold): JsonResponse
    {
        try {
            $confirmed = $this->slotService->confirmHold($hold);
        } catch (SlotUnavailableException|HoldNotConfirmableException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 409);
        }

        return response()->json($this->formatHold($confirmed));
    }

    public function destroy(Hold $hold): JsonResponse
    {
        $cancelled = $this->slotService->cancelHold($hold);

        return response()->json($this->formatHold($cancelled));
    }

    private function formatHold(Hold $hold): array
    {
        return [
            'id' => $hold->id,
            'slot_id' => $hold->slot_id,
            'status' => $hold->status,
            'expires_at' => optional($hold->expires_at)->toIso8601String(),
            'confirmed_at' => optional($hold->confirmed_at)->toIso8601String(),
            'cancelled_at' => optional($hold->cancelled_at)->toIso8601String(),
            'created_at' => optional($hold->created_at)->toIso8601String(),
            'updated_at' => optional($hold->updated_at)->toIso8601String(),
        ];
    }
}
