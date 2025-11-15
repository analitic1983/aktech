<?php

namespace App\Modules\Slots\Services;

use App\Exceptions\HoldNotConfirmableException;
use App\Exceptions\SlotUnavailableException;
use App\Modules\Slots\Dto\SlotAvailabilityDto;
use App\Modules\Slots\Dto\SlotsAvailabilityDto;
use App\Modules\Slots\Enums\HoldStatusEnum;
use App\Modules\Slots\Factories\HoldFactory;
use App\Modules\Slots\Factories\SlotFactory;
use App\Modules\Slots\Models\Hold;
use App\Modules\Slots\Models\Slot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

readonly class SlotService
{
    public function __construct(
        private SlotFactory $slotFactory,
        private HoldFactory $holdFactory,
        private CacheInvalidatorService $cacheInvalidatorService
    ) {
    }

    /**
     * Return slots availability, cached for a short random interval.
     */
    public function getAvailability(): SlotsAvailabilityDto
    {
        $slots = Slot::query()->get();
        $availability = [];
        foreach ($slots as $slot) {
            /** @var Slot $slot */
            $availability[] = new SlotAvailabilityDto(
                $slot->uuid,
                $slot->capacity,
                $slot->capacity - $slot->used
            );
        }

        return new SlotsAvailabilityDto($availability);
    }


    public function createHold(string $slotUuid): Hold
    {
        DB::beginTransaction();

        try {
            /** @var Slot $slot */
            $slot = Slot::query()
                ->lockForUpdate()
                ->findOrBusinessFail($slotUuid);

            if ($slot->capacity <= $slot->used) {
                throw new SlotUnavailableException('Свободных мест в слоте не осталось.');
            }

            $hold = $this->holdFactory->create(
                slotUuid: $slot->uuid,
                status: HoldStatusEnum::HELD,
                expiresAt:  CarbonImmutable::now()->addMinutes(5),
            );

            $hold->save();
            $slot->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->cacheInvalidatorService->invalidateAvailabilityCache();
        return $hold;
    }

    public function confirmHold(string $holdUuid): Hold
    {
        DB::beginTransaction();

        try {
            /** @var Hold $hold */
            $hold = Hold::query()
                ->lockForUpdate()
                ->findOrBusinessFail($holdUuid);

            if ($hold->isConfirmed()) {
                DB::commit();
                return $hold;
            }

            if (!$hold->isHeld()) {
                throw new HoldNotConfirmableException(
                    'Hold не может быть подтверждён в текущем состоянии'
                );
            }
            if ($hold->isExpired()) {
                throw new HoldNotConfirmableException(
                    'Срок действия Hold истек'
                );
            }

            /** @var Slot $slot */
            $slot = Slot::query()
                ->lockForUpdate()
                ->findOrBusinessFail($hold->slot_uuid);

            if ($slot->capacity <= $slot->used) {
                throw new SlotUnavailableException(
                    'Свободных мест в слоте больше нет.'
                );
            }

            $slot->used = $slot->used + 1;
            
            $hold->confirm();
            
            $hold->save();
            $slot->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->cacheInvalidatorService->invalidateAvailabilityCache();
        return $hold;
    }

    public function cancelHold(string $holdUuid): Hold
    {

        DB::beginTransaction();

        try {
            /** @var Hold $hold */
            $hold = Hold::query()
                ->lockForUpdate()
                ->findOrBusinessFail($holdUuid);

            if ($hold->isCanceled()) {
                DB::commit();
                return $hold;
            }

            /** @var Slot $slot */
            $slot = Slot::query()
                ->lockForUpdate()
                ->findOrBusinessFail($hold->slot_uuid);

            if ($hold->isConfirmed()) {
                $slot->used = $slot->used - 1;
            }

            $hold->cancel();

            $hold->save();
            $slot->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->cacheInvalidatorService->invalidateAvailabilityCache();
        return $hold;
    }

    public function createSlot(int $capacity): Slot
    {
        $slot = $this->slotFactory->create($capacity);
        $slot->save();

        return $slot;
    }
}
