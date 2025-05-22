<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Infrastructure\Auth\TokenService;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Registrar listeners para eventos de autenticación para depurar
        Event::listen(Attempting::class, function ($event) {
            Log::channel('auth')->debug('Intento de autenticación', [
                'guard' => $event->guard,
                'credentials' => [
                    'email' => $event->credentials['email'] ?? 'N/A',
                ],
            ]);
        });

        Event::listen(Authenticated::class, function ($event) {
            Log::channel('auth')->debug('Autenticación exitosa', [
                'guard' => $event->guard,
                'user_id' => $event->user->id ?? 'N/A',
            ]);
        });

        Event::listen(Failed::class, function ($event) {
            Log::channel('auth')->debug('Fallo de autenticación', [
                'guard' => $event->guard,
                'credentials' => [
                    'email' => $event->credentials['email'] ?? 'N/A',
                ],
            ]);
        });
    }

    public function register(): void
    {
        $this->app->singleton(TokenService::class, function ($app) {
            return new TokenService();
        });

        // Agregar middleware personalizado para depurar Passport
        $this->app->bind(\Laravel\Passport\Http\Middleware\CheckCredentials::class, function ($app) {
            $auth = $app->make(\Illuminate\Contracts\Auth\Guard::class);
            $tokens = $app->make(\Laravel\Passport\TokenRepository::class);
            $clients = $app->make(\Laravel\Passport\ClientRepository::class);
            $providers = $app->make(\Illuminate\Contracts\Auth\UserProvider::class);

            $middleware = new \Laravel\Passport\Http\Middleware\CheckCredentials($auth, $tokens, $clients, $providers);

            // Wrap el middleware en un closure para logging
            return function ($request, $next) use ($middleware) {
                try {
                    Log::channel('auth')->debug('CheckCredentials middleware ejecutándose');

                    return $middleware($request, $next);
                } catch (\Exception $e) {
                    Log::channel('auth')->error('CheckCredentials error', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    throw $e;
                }
            };
        });
    }
}
