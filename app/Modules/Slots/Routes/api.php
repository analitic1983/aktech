<?php

use App\Modules\Slots\Controllers\SlotsController;
use App\Modules\Slots\Controllers\HoldController;
use App\Modules\Slots\Enums\IdempotencyOperationEnum;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('api')->group(function () {
    Route::get('/slots/availability', [SlotsController::class, 'availability']);
    Route::post('/slots/{slotUuid}/hold', [HoldController::class, 'hold'])
        ->middleware('idempotency:' . IdempotencyOperationEnum::HOLD->value);
    Route::post('/holds/{holdUuid}/confirm', [HoldController::class, 'confirm'])
        ->middleware('idempotency:' . IdempotencyOperationEnum::CONFIRM->value);
    Route::post('/holds/{holdUuid}/cancel', [HoldController::class, 'cancel'])
        ->middleware('idempotency:' . IdempotencyOperationEnum::CANCEL->value);
});
