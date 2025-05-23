<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Domain\Exceptions\Giphy\GiphyNotFoundException;
use App\Domain\Exceptions\Giphy\GiphyRequestException;
use App\Domain\Exceptions\Giphy\GiphyResponseException;
use App\Domain\Interfaces\GiphyServiceInterface;
use App\Infrastructure\Http\Requests\Giphy\SearchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Info(
 *     title="API Giphy",
 *     version="1.0.0",
 *     description="API para interactuar con Giphy y gestionar favoritos",
 *     @OA\Contact(
 *         email="andres.echavalete.dev@gmail.com",
 *         name="Andres Echavalete"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\Tag(
 *     name="Giphy",
 *     description="Operaciones con la API de Giphy"
 * )
 */
readonly class GiphyController
{
    /**
     * @param GiphyServiceInterface $giphyService
     */
    public function __construct(
        private GiphyServiceInterface $giphyService
    ) {
    }

    /**
     * Buscar GIFs en Giphy
     * 
     * @OA\Get(
     *     path="/api/gifs/search",
     *     tags={"Giphy"},
     *     summary="Buscar GIFs por término",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Término de búsqueda",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
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
     *         description="Listado de GIFs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=503,
     *         description="Servicio no disponible",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error_code", type="string", example="SERVICE_UNAVAILABLE")
     *         )
     *     )
     * )
     * 
     * @param SearchRequest $request
     * @return JsonResponse
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $query = $request->input('query');
        $limit = (int) $request->input('limit', 25);
        $offset = (int) $request->input('offset', 0);

        try {
            $results = $this->giphyService->search($query, $limit, $offset);

            return response()->json([
                'success' => true,
                'data' => $results['data'] ?? [],
                'pagination' => $results['pagination'] ?? [
                    'count' => count($results['data'] ?? []),
                    'offset' => $offset,
                    'total_count' => count($results['data'] ?? []),
                ],
            ]);
        } catch (GiphyNotFoundException $e) {
            // No se encontraron resultados o recurso no disponible
            Log::info('No se encontraron GIFs para la búsqueda', [
                'query' => $query,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [],
                'pagination' => [
                    'count' => 0,
                    'offset' => $offset,
                    'total_count' => 0,
                ],
                'message' => 'No se encontraron resultados para la búsqueda',
            ]);
        } catch (GiphyRequestException $e) {
            // Error de solicitud a la API
            Log::warning('Error en solicitud a API de Giphy', [
                'message' => $e->getMessage(),
                'query' => $query,
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error en la comunicación con el servicio externo',
                'error_code' => 'SERVICE_UNAVAILABLE',
            ], 503);
        } catch (GiphyResponseException $e) {
            // Error en la respuesta de la API
            Log::error('Error en respuesta de API de Giphy', [
                'message' => $e->getMessage(),
                'query' => $query,
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'El servicio externo no respondió correctamente',
                'error_code' => 'EXTERNAL_SERVICE_ERROR',
            ], 502);
        } catch (\Throwable $e) {
            // Error inesperado
            Log::error('Error inesperado en búsqueda de GIFs', [
                'message' => $e->getMessage(),
                'query' => $query,
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar la solicitud',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Obtener un GIF por su ID
     * 
     * @OA\Get(
     *     path="/api/gifs/{id}",
     *     tags={"Giphy"},
     *     summary="Obtener GIF por ID",
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
     *         description="GIF encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="GIF no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="GIF no encontrado"),
     *             @OA\Property(property="error_code", type="string", example="NOT_FOUND")
     *         )
     *     )
     * )
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $gif = $this->giphyService->getById($id);

            if (empty($gif)) {
                return response()->json([
                    'success' => false,
                    'message' => 'GIF no encontrado',
                    'error_code' => 'NOT_FOUND',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $gif,
            ]);
        } catch (GiphyNotFoundException $e) {
            // GIF no encontrado
            Log::info('GIF no encontrado', [
                'id' => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'GIF no encontrado',
                'error_code' => 'NOT_FOUND',
            ], 404);
        } catch (GiphyRequestException $e) {
            // Error de solicitud a la API
            Log::warning('Error en solicitud a API de Giphy', [
                'message' => $e->getMessage(),
                'id' => $id,
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error en la comunicación con el servicio externo',
                'error_code' => 'SERVICE_UNAVAILABLE',
            ], 503);
        } catch (GiphyResponseException $e) {
            // Error en la respuesta de la API
            Log::error('Error en respuesta de API de Giphy', [
                'message' => $e->getMessage(),
                'id' => $id,
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'El servicio externo no respondió correctamente',
                'error_code' => 'EXTERNAL_SERVICE_ERROR',
            ], 502);
        } catch (\Throwable $e) {
            // Error inesperado
            Log::error('Error inesperado al obtener GIF por ID', [
                'message' => $e->getMessage(),
                'id' => $id,
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar la solicitud',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }
}
