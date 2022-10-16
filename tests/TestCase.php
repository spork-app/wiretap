<?php

namespace Spork\Wiretap\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Spork\Wiretap\WiretapServiceProvider;

class TestCase extends TestbenchTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Spork\\Wiretap\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            WiretapServiceProvider::class,
        ];
    }
}
