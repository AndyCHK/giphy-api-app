<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Domain\Interfaces\GiphyServiceInterface;
use App\Infrastructure\Http\Requests\Giphy\SearchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
        } catch (\Throwable $e) {
            Log::error('Error en búsqueda de GIFs', [
                'message' => $e->getMessage(),
                'query' => $query,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar GIFs',
            ], 500);
        }
    }

    /**
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
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $gif,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener GIF por ID', [
                'message' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener GIF',
            ], 500);
        }
    }
}
