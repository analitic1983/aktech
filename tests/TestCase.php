<?php

namespace Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected Container $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Container();
        Container::setInstance($this->app);
        Facade::setFacadeApplication($this->app);

        $this->app->instance('config', new Repository([
            'slots' => [
                'availability_cache_key' => 'slots:availability',
            ],
        ]));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        Facade::clearResolvedInstances();
        Container::setInstance(null);
        unset($this->app);

        parent::tearDown();
    }
}
