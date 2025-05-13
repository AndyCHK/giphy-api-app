<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Interfaces\GiphyServiceInterface;
use App\Infrastructure\External\Giphy\GiphyApiClient;
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

        $this->app->singleton(GiphyServiceInterface::class, function ($app) {
            return new GiphyApiClient(
                config: $app->make(GiphyConfig::class),
                transformer: $app->make(GiphyResponseTransformer::class)
            );
        });
    }


    public function boot(): void
    {
        //
    }
}
