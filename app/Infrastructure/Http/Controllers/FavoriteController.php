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

/**
 * @OA\Tag(
 *     name="Favoritos",
 *     description="Operaciones con GIFs favoritos"
 * )
 * 
 * @OA\Schema(
 *     schema="FavoriteDTO",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=123),
 *     @OA\Property(property="gif_id", type="string", example="xT9IgDEI1iZyb2wqo8"),
 *     @OA\Property(property="alias", type="string", example="Mi GIF favorito"),
 *     @OA\Property(property="original_gif_id", type="string", example="xT9IgDEI1iZyb2wqo8"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
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
     * Guardar un GIF como favorito
     * 
     * @OA\Post(
     *     path="/api/favorites",
     *     summary="Guardar un GIF como favorito",
     *     tags={"Favoritos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="gif_id", type="string", example="xT9IgDEI1iZyb2wqo8"),
     *             @OA\Property(property="alias", type="string", example="Mi GIF favorito"),
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="GIF guardado como favorito (sin contenido)"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     * 
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
     * Obtener lista de GIFs favoritos
     * 
     * @OA\Get(
     *     path="/api/favorites",
     *     summary="Obtener lista de GIFs favoritos",
     *     tags={"Favoritos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de resultados",
     *         required=false,
     *         @OA\Schema(type="integer", default=25)
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Índice para paginación",
     *         required=false,
     *         @OA\Schema(type="integer", default=0)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de favoritos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/FavoriteDTO")
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="count", type="integer"),
     *                 @OA\Property(property="offset", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     * 
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
     * Eliminar un GIF de favoritos
     * 
     * @OA\Delete(
     *     path="/api/favorites/{id}",
     *     summary="Eliminar un GIF de favoritos",
     *     tags={"Favoritos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del GIF",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="GIF eliminado de favoritos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="GIF eliminado de favoritos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="GIF no encontrado en favoritos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="GIF no encontrado en favoritos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     * 
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
