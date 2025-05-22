<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Auth;

use App\Application\UseCases\Auth\LoginUserUseCase;
use App\Application\UseCases\Auth\RegisterUserUseCase;
use App\Domain\DTOs\Auth\LoginUserDTO;
use App\Domain\DTOs\Auth\RegisterUserDTO;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\UserAlreadyExistsException;
use App\Infrastructure\Auth\TokenService;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\Auth\LoginRequest;
use App\Infrastructure\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;

final class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterUserUseCase $registerUserUseCase,
        private readonly LoginUserUseCase $loginUserUseCase,
        private readonly TokenRepository $tokenRepository,
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly TokenService $tokenService
    ) {
    }

    /**
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->registerUserUseCase->execute(
                RegisterUserDTO::fromRequest($request->validated())
            );

            $token = $this->tokenService->createToken($user, 'Personal Access Token');

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'user' => [
                        'id' => $user->id(),
                        'name' => $user->name(),
                        'email' => (string) $user->email(),
                        'roles' => $user->roles(),
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (UserAlreadyExistsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = $this->loginUserUseCase->execute(
                LoginUserDTO::fromRequest($request->validated())
            );

            $token = $this->tokenService->createToken($user, 'Personal Access Token');

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'user' => [
                        'id' => $user->id(),
                        'name' => $user->name(),
                        'email' => (string) $user->email(),
                        'roles' => $user->roles(),
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (InvalidCredentialsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            $bearerToken = request()->bearerToken();

            if (! $bearerToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se proporcionó token de autenticación',
                ], 401);
            }

            $tokenVerification = $this->tokenService->verifyToken($bearerToken);

            if (! $tokenVerification['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido: ' . $tokenVerification['error'],
                ], 401);
            }

            $tokenId = $tokenVerification['token_id'];

            $this->tokenRepository->revokeAccessToken($tokenId);
            $this->refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error en el proceso de logout', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function list(): ?JsonResponse
    {
        return null;
    }
}
