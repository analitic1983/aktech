<?php

use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\HoldController;
use Illuminate\Support\Facades\Route;

Route::get('/slots/availability', [AvailabilityController::class, 'index']);
Route::post('/slots/{slot}/hold', [HoldController::class, 'store']);
Route::post('/holds/{hold}/confirm', [HoldController::class, 'confirm']);
Route::delete('/holds/{hold}', [HoldController::class, 'destroy']);
