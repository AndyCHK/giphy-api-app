<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Infrastructure\Auth\TokenService;
use App\Infrastructure\Persistence\Eloquent\Models\EloquentUser;

class ApiAuthenticate extends Middleware
{
    protected $tokenService;
    
    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
        parent::__construct(app('auth'));
    }
    
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }

    public function handle($request, Closure $next, ...$guards)
    {
        if (!$request->bearerToken()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Bearer token no proporcionado.'
            ], 401);
        }

        try {
            $token = $request->bearerToken();
            
            $tokenVerification = $this->tokenService->verifyToken($token);
            
            if (!$tokenVerification['valid']) {
                Log::warning('Token JWT inválido', [
                    'token_error' => $tokenVerification['error'],
                    'route' => $request->route()->getName(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido: ' . $tokenVerification['error']
                ], 401);
            }
            
            $userId = $tokenVerification['user_id'];
            $user = EloquentUser::find($userId);
            
            if (!$user) {
                Log::error('Usuario no encontrado para token válido', [
                    'user_id' => $userId,
                    'token_id' => $tokenVerification['token_id'],
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 401);
            }
            
            Auth::guard('api')->setUser($user);
            
            return $next($request);
            
        } catch (\Exception $e) {
            Log::warning('Error de autenticación API', [
                'message' => $e->getMessage(),
                'route' => $request->path(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error de autenticación: ' . $e->getMessage()
            ], 401);
        }
    }
} 