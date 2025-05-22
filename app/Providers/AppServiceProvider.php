<?php

namespace App\Providers;

use App\Domain\Interfaces\GiphyIdAdapterServiceInterface;
use App\Domain\Interfaces\GiphyIdMappingRepositoryInterface;
use App\Domain\Services\GiphyIdAdapterService;
use App\Infrastructure\Persistence\Eloquent\EloquentGiphyIdMappingRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            GiphyIdMappingRepositoryInterface::class,
            EloquentGiphyIdMappingRepository::class
        );
        $this->app->bind(
            GiphyIdAdapterServiceInterface::class,
            GiphyIdAdapterService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
