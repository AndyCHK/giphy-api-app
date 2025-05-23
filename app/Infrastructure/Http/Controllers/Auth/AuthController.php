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

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Operaciones de autenticación"
 * )
 *  
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\Schema(
 *     schema="UserDTO",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="1"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *     @OA\Property(property="roles", type="array", @OA\Items(type="string", example="admin"))
 * )
 * 
 * @OA\Schema(
 *     schema="RegisterUserDTO",
 *     type="object",
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *     @OA\Property(property="password", type="string", example="Password123"),
 *     @OA\Property(property="password_confirmation", type="string", example="Password123")
 * )
 * 
 * @OA\Schema(
 *     schema="LoginUserDTO",
 *     type="object",
 *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *     @OA\Property(property="password", type="string", example="Password123")
 * )
 */

final class AuthController extends Controller
{
    /**
     * @param RegisterUserUseCase $registerUserUseCase
     * @param LoginUserUseCase $loginUserUseCase
     * @param TokenRepository $tokenRepository
     * @param RefreshTokenRepository $refreshTokenRepository
     * @param TokenService $tokenService
     */
    public function __construct(
        private readonly RegisterUserUseCase $registerUserUseCase,
        private readonly LoginUserUseCase $loginUserUseCase,
        private readonly TokenRepository $tokenRepository,
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly TokenService $tokenService
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Registrar un nuevo usuario",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RegisterUserDTO")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Usuario ya existe"
     *     )
     * )
     * 
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
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Iniciar sesión",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginUserDTO")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales inválidas"
     *     )
     * )
     * 
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
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Cerrar sesión",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Cierre de sesión exitoso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No se proporcionó token de autenticación"
     *     )
     * )
     * 
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

}
