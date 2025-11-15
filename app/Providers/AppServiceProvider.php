<?php

namespace App\Providers;

use App\Exceptions\Handler as AppExceptionHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ExceptionHandler::class, AppExceptionHandler::class);

        $this->app->singleton(SerializerInterface::class, function () {
            return new Serializer(
                normalizers: [
                    new ObjectNormalizer(),
                    new ArrayDenormalizer(),
                ],
                encoders: [] // encoders не нужны, мы используем только normalize()
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
