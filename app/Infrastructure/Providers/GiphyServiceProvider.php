<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Interfaces\GiphyServiceInterface;
use App\Infrastructure\External\Giphy\GiphyApiClient;
use App\Infrastructure\External\Giphy\GiphyCacheService;
use App\Infrastructure\External\Giphy\GiphyCircuitBreaker;
use App\Infrastructure\External\Giphy\GiphyConfig;
use App\Infrastructure\External\Giphy\GiphyResponseTransformer;
use Illuminate\Support\ServiceProvider;

class GiphyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GiphyConfig::class, function ($app) {
            return GiphyConfig::fromConfig();
        });

        $this->app->singleton(GiphyResponseTransformer::class);

        $this->app->singleton(GiphyCacheService::class);

        $this->app->singleton(GiphyCircuitBreaker::class, function ($app) {
            return new GiphyCircuitBreaker(
                $app->make(GiphyConfig::class)
            );
        });

        $this->app->singleton(GiphyServiceInterface::class, function ($app) {
            return new GiphyApiClient(
                config: $app->make(GiphyConfig::class),
                transformer: $app->make(GiphyResponseTransformer::class),
                cacheService: $app->make(GiphyCacheService::class),
                circuitBreaker: $app->make(GiphyCircuitBreaker::class)
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../../config/giphy.php' => config_path('giphy.php'),
        ], 'giphy-config');
    }
}
