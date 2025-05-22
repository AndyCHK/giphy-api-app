<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Interfaces\ApiInteractionRepositoryInterface;
use App\Domain\Interfaces\GifRepositoryInterface;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\EloquentApiInteractionRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentGifRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(GifRepositoryInterface::class, EloquentGifRepository::class);
        $this->app->bind(ApiInteractionRepositoryInterface::class, EloquentApiInteractionRepository::class);
    }
}
