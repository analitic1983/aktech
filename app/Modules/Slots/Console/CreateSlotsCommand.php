<?php

namespace App\Modules\Slots\Console;

use App\Modules\Slots\Services\SlotService;
use Illuminate\Console\Command;

class CreateSlotsCommand extends Command
{
    /** @var string Команда, вызываемая из консоли */
    protected $signature = 'slots:create {count=3}';

    /** @var string Описание команды */
    protected $description = 'Создать указанное количество слотов (по‑умолчанию 3)';

    public function __construct(private readonly SlotService $slotService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = (int) $this->argument('count');

        for ($i = 0; $i < $count; $i++) {
            $this->slotService->createSlot(random_int(5, 10));
            $this->info('Slot #' . ($i + 1) . ' создан.');
        }

        return self::SUCCESS;
    }
}
