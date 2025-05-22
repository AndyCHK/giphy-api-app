<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Domain\Interfaces\GifRepositoryInterface;
use App\Domain\Interfaces\GiphyIdAdapterServiceInterface;
use App\Infrastructure\Http\Requests\Favorite\SaveFavoriteRequest;
use App\Infrastructure\Persistence\Eloquent\Models\EloquentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

readonly class FavoriteController
{
    /**
     * @param GifRepositoryInterface $gifRepository
     */
    public function __construct(
        private GifRepositoryInterface $gifRepository
    ) {
    }

    /**
     * @param SaveFavoriteRequest $request
     * @return JsonResponse
     */
    public function store2(SaveFavoriteRequest $request): JsonResponse
    {
        $gifId = $request->input('gif_id');
        $alias = $request->input('alias');
        $userId = Auth::guard('api')->id();

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado',
            ], 401);
        }

        try {
            $user = EloquentUser::find($userId);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar favorito: usuario no encontrado',
                ], 500);
            }

            $result = $this->gifRepository->saveFavorite($gifId, $userId, $alias);

            if (! $result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo guardar el favorito',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'GIF guardado como favorito',
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error al guardar favorito', [
                'message' => $e->getMessage(),
                'gif_id' => $gifId,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar favorito',
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @param GiphyIdAdapterServiceInterface $adapter
     * @return Response
     */
    public function store(Request $request, GiphyIdAdapterServiceInterface $adapter)
    {
        $request->validate([
            'gif_id' => 'required|string', // Aceptamos el ID alfanumérico
            'alias' => 'required|string',
            'user_id' => 'required|numeric',
        ]);

        // Convertir a ID numérico
        $numericGifId = $adapter->toNumericId($request->gif_id);

        // Guardar favorito con ID numérico
        $this->gifRepository->saveFavorite(
            $request->user_id,
            $numericGifId, // Ahora es numérico
            $request->alias
        );

        return response()->noContent();
    }

    /**
     * @param Request $request
     * @param GiphyIdAdapterServiceInterface $adapter
     * @return JsonResponse
     */
    public function index(Request $request, GiphyIdAdapterServiceInterface $adapter): JsonResponse
    {
        $limit = (int) $request->input('limit', 25);
        $offset = (int) $request->input('offset', 0);

        $userId = Auth::guard('api')->id();

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado',
            ], 401);
        }

        try {
            $favorites = $this->gifRepository->getFavorites($userId, $limit, $offset);

            // Convertir los IDs numéricos de vuelta a IDs alfanuméricos de Giphy
            $favoritesWithOriginalIds = array_map(function ($favorite) use ($adapter) {
                if (isset($favorite['id']) && is_numeric($favorite['id'])) {
                    $favorite['original_gif_id'] = $adapter->toGiphyId((int)$favorite['id']);
                }

                return $favorite;
            }, $favorites);

            return response()->json([
                'success' => true,
                'data' => $favoritesWithOriginalIds,
                'pagination' => [
                    'count' => count($favorites),
                    'offset' => $offset,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener favoritos', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener favoritos',
            ], 500);
        }
    }

    /**
     * @param string $id
     * @param GiphyIdAdapterServiceInterface $adapter
     * @return JsonResponse
     */
    public function destroy(string $id, GiphyIdAdapterServiceInterface $adapter): JsonResponse
    {
        $userId = Auth::guard('api')->id();

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado',
            ], 401);
        }

        try {
            // Convertir ID alfanumérico a numérico
            $numericGifId = $adapter->toNumericId($id);

            if (! $this->gifRepository->isFavorite($numericGifId, $userId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'GIF no encontrado en favoritos',
                ], 404);
            }

            $result = $this->gifRepository->removeFavorite($numericGifId, $userId);

            if (! $result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo eliminar el favorito',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'GIF eliminado de favoritos',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar favorito', [
                'message' => $e->getMessage(),
                'gif_id' => $id,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar favorito',
            ], 500);
        }
    }
}
