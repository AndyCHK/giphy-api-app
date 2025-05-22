<?php

namespace App\Infrastructure\Http\Controllers;

use App\Domain\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserController
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Obtener listado de usuarios
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        try {
            $users = $this->userRepository->all();

            $formattedUsers = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedUsers,
                'count' => $formattedUsers->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener usuarios', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los usuarios',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
