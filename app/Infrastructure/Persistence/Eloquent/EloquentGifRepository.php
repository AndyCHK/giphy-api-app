<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Interfaces\GifRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\EloquentFavoriteGif;
use App\Infrastructure\Persistence\Eloquent\Models\EloquentUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EloquentGifRepository implements GifRepositoryInterface
{
    /**
     * @param string $gifId
     * @param int|string $userId
     * @param string $alias
     * @return bool
     */
    public function saveFavorite(string $gifId, $userId, string $alias = ''): bool
    {
        try {
            $user = EloquentUser::find($userId);
            
            if (!$user) {
                Log::error('Usuario no encontrado al guardar favorito', [
                    'user_id' => $userId,
                    'gif_id' => $gifId
                ]);
                return false;
            }
            
            $exists = EloquentFavoriteGif::where('user_id', $user->id)
                ->where('gif_id', $gifId)
                ->exists();
                
            if ($exists) {
                return true;
            }
            
            $favorite = new EloquentFavoriteGif();
            $favorite->user_id = $user->id;
            $favorite->gif_id = $gifId;
            $favorite->alias = $alias;
            
            $result = $favorite->save();
            
            return $result;
        } catch (\Throwable $e) {
            Log::error('Error al guardar favorito', [
                'message' => $e->getMessage(),
                'gif_id' => $gifId,
                'user_id' => $userId
            ]);
            return false;
        }
    }

    /**
     * @param string $gifId
     * @param int|string $userId
     * @return bool
     */
    public function removeFavorite(string $gifId, $userId): bool
    {
        try {
            $user = EloquentUser::find($userId);
            
            if (!$user) {
                Log::error('Usuario no encontrado al eliminar favorito', [
                    'user_id' => $userId,
                    'gif_id' => $gifId
                ]);
                return false;
            }
            
            $exists = EloquentFavoriteGif::where('user_id', $user->id)
                ->where('gif_id', $gifId)
                ->exists();
                
            if (!$exists) {
                return false;
            }
            
            try {
                $deleted = EloquentFavoriteGif::where('user_id', $user->id)
                    ->where('gif_id', $gifId)
                    ->delete();
                    
                return $deleted > 0;
            } catch (\Throwable $dbError) {
                Log::error('Error en la operación de eliminación', [
                    'message' => $dbError->getMessage(),
                    'gif_id' => $gifId,
                    'user_id' => $user->id
                ]);
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('Error al eliminar favorito', [
                'message' => $e->getMessage(),
                'gif_id' => $gifId,
                'user_id' => $userId
            ]);
            return false;
        }
    }

    /**
     * @param int|string $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFavorites($userId, int $limit = 25, int $offset = 0): array
    {
        try {
            $user = EloquentUser::find($userId);
            
            if (!$user) {
                return [];
            }
            
            $favorites = EloquentFavoriteGif::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();
                
            return $favorites->map(function ($favorite) {
                return [
                    'id' => $favorite->gif_id,
                    'alias' => $favorite->alias,
                    'created_at' => $favorite->created_at->toIso8601String(),
                ];
            })->toArray();
        } catch (\Throwable $e) {
            Log::error('Error al obtener favoritos', [
                'message' => $e->getMessage(),
                'user_id' => $userId
            ]);
            return [];
        }
    }

    /**
     * @param string $gifId
     * @param int|string $userId
     * @return bool
     */
    public function isFavorite(string $gifId, $userId): bool
    {
        try {
            $user = EloquentUser::find($userId);
            
            if (!$user) {
                return false;
            }
            
            try {
                $exists = EloquentFavoriteGif::where('user_id', $user->id)
                    ->where('gif_id', $gifId)
                    ->exists();
                    
                return $exists;
            } catch (\Throwable $dbError) {
                Log::error('Error en consulta de verificación de favorito', [
                    'message' => $dbError->getMessage(),
                    'gif_id' => $gifId,
                    'user_id' => $user->id
                ]);
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('Error al verificar favorito', [
                'message' => $e->getMessage(),
                'gif_id' => $gifId,
                'user_id' => $userId
            ]);
            return false;
        }
    }
} 