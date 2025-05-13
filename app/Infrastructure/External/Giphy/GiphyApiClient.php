<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Giphy;

use App\Domain\DTOs\Giphy\GifDTO;
use App\Domain\DTOs\Giphy\GifsCollectionDTO;
use App\Domain\Exceptions\Giphy\GiphyNotFoundException;
use App\Domain\Exceptions\Giphy\GiphyRequestException;
use App\Domain\Exceptions\Giphy\GiphyResponseException;
use App\Domain\Interfaces\GiphyServiceInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GiphyApiClient implements GiphyServiceInterface
{
    private PendingRequest $httpClient;

    /**
     * @param GiphyConfig $config
     * @param GiphyResponseTransformer $transformer
     */
    public function __construct(
        private readonly GiphyConfig $config,
        private readonly GiphyResponseTransformer $transformer
    ) {
        $this->httpClient = Http::timeout($config->getTimeout())
            ->retry($config->getRetryAttempts(), $config->getRetryDelay())
            ->baseUrl($config->getBaseUrl());
    }

    /**
     * @throws GiphyRequestException
     * @throws Throwable
     * @throws GiphyResponseException
     * @throws GiphyNotFoundException
     */
    public function search(string $query, int $limit = 25, int $offset = 0): array
    {
        try {
            $response = $this->httpClient->get('/gifs/search', [
                'api_key' => $this->config->getApiKey(),
                'q' => $query,
                'limit' => $limit,
                'offset' => $offset,
                'rating' => 'g',
                'lang' => 'es'
            ]);

            $collection = $this->transformer->transformSearchResponse($response);

            return $collection->toArray();
        } catch (Throwable $e) {
            $this->handleException($e, "Error al buscar GIFs: $query");
            throw $e;
        }
    }

    /**
     * @throws GiphyRequestException
     * @throws Throwable
     * @throws GiphyResponseException
     * @throws GiphyNotFoundException
     */
    public function getById(string $id): ?array
    {
        try {
            $response = $this->httpClient->get('/gifs/' . $id, [
                'api_key' => $this->config->getApiKey(),
            ]);

            $gif = $this->transformer->transformGetByIdResponse($response);

            return $gif->toArray();
        } catch (Throwable $e) {
            $this->handleException($e, "Error al obtener GIF por ID: $id");
            throw $e;
        }
    }

    /**
     * Este método no está implementado ya que no forma parte de los requisitos del Challenge
     * @throws \Exception
     */
    public function getTrending(int $limit = 25, int $offset = 0): array
    {
        throw new \Exception('Método no implementado. Esta funcionalidad no forma parte de los requisitos del Challenge.');
    }

    /**
     * @throws GiphyRequestException
     */
    private function handleException(Throwable $exception, string $context): void
    {
        Log::error("GIPHY API Error: $context", [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Si es una excepción foranea del dominio, afuera!!
        if (!$exception instanceof GiphyRequestException) {
            throw new GiphyRequestException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
