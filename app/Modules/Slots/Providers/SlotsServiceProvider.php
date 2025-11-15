<?php

namespace App\Modules\Slots\Providers;

use App\Modules\Slots\Console\CreateSlotsCommand;
use Illuminate\Support\ServiceProvider;

class SlotsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateSlotsCommand::class,
            ]);
        }
    }
}
