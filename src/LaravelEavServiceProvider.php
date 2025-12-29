<?php

namespace Fiachehr\LaravelEav;

use Fiachehr\LaravelEav\Application\UseCases\CreateAttributeGroupUseCase;
use Fiachehr\LaravelEav\Application\UseCases\CreateAttributeUseCase;
use Fiachehr\LaravelEav\Application\UseCases\UpdateAttributeUseCase;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeGroupRepositoryInterface;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeRepositoryInterface;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeValueRepositoryInterface;
use Fiachehr\LaravelEav\Infrastructure\Repositories\EloquentAttributeGroupRepository;
use Fiachehr\LaravelEav\Infrastructure\Repositories\EloquentAttributeRepository;
use Fiachehr\LaravelEav\Infrastructure\Repositories\EloquentAttributeValueRepository;
use Illuminate\Support\ServiceProvider;

class LaravelEavServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            AttributeRepositoryInterface::class,
            EloquentAttributeRepository::class
        );

        $this->app->bind(
            AttributeGroupRepositoryInterface::class,
            EloquentAttributeGroupRepository::class
        );

        $this->app->bind(
            AttributeValueRepositoryInterface::class,
            EloquentAttributeValueRepository::class
        );

        // Bind Use Cases
        $this->app->bind(CreateAttributeUseCase::class, function ($app) {
            return new CreateAttributeUseCase(
                $app->make(AttributeRepositoryInterface::class)
            );
        });

        $this->app->bind(UpdateAttributeUseCase::class, function ($app) {
            return new UpdateAttributeUseCase(
                $app->make(AttributeRepositoryInterface::class)
            );
        });

        $this->app->bind(CreateAttributeGroupUseCase::class, function ($app) {
            return new CreateAttributeGroupUseCase(
                $app->make(AttributeGroupRepositoryInterface::class)
            );
        });

        // Register EAV Validation Service
        $this->app->singleton(
            \Fiachehr\LaravelEav\Application\Services\EavValidationService::class,
            function ($app) {
                return new \Fiachehr\LaravelEav\Application\Services\EavValidationService(
                    $app->make(AttributeRepositoryInterface::class),
                    $app->make(\Fiachehr\LaravelEav\Domain\Services\AttributeValidator::class)
                );
            }
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $migrationsPath = __DIR__ . '/Database/Migrations';
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }

        $configPath = dirname(__DIR__) . '/config/laravel-eav.php';
        if (file_exists($configPath) && $this->app->bound('config')) {
            $config = require $configPath;
            if (is_array($config)) {
                $this->mergeConfigFrom($configPath, 'laravel-eav');
            }

            $this->publishes([
                $configPath => config_path('laravel-eav.php'),
            ], 'laravel-eav-config');
        }
    }
}
