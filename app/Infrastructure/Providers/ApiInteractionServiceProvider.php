<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Interfaces\ApiInteractionRepositoryInterface;
use App\Domain\Services\ApiInteractionService;
use Illuminate\Support\ServiceProvider;

class ApiInteractionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApiInteractionService::class, function ($app) {
            return new ApiInteractionService(
                $app->make(ApiInteractionRepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
