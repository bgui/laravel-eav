<?php

namespace Fiachehr\LaravelEav\Tests;

use Fiachehr\LaravelEav\LaravelEavServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set default locale for tests
        app()->setLocale('en');
        
        // Run migrations
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/Migrations');
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelEavServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        
        // Set supported locales
        $app['config']->set('app.locale', 'en');
        $app['config']->set('app.supported_locales', ['en', 'fa', 'de', 'es']);
    }
}

