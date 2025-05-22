<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Giphy;

use App\Domain\Exceptions\Giphy\GiphyApiException;
use App\Domain\Exceptions\Giphy\GiphyNotFoundException;
use App\Domain\Exceptions\Giphy\GiphyRequestException;
use App\Domain\Exceptions\Giphy\GiphyResponseException;
use App\Domain\Interfaces\GiphyServiceInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GiphyApiClient implements GiphyServiceInterface
{
    private PendingRequest $httpClient;

    /**
     * @param GiphyConfig $config
     * @param GiphyResponseTransformer $transformer
     * @param GiphyCacheService $cacheService
     * @param GiphyCircuitBreaker $circuitBreaker
     */
    public function __construct(
        private readonly GiphyConfig $config,
        private readonly GiphyResponseTransformer $transformer,
        private readonly GiphyCacheService $cacheService,
        private readonly GiphyCircuitBreaker $circuitBreaker,
    ) {
        $this->httpClient = Http::timeout($config->getTimeout())
            ->retry($config->getRetryAttempts(), $config->getRetryDelay(), function ($exception, $request) {
                // Solo reintentar en ciertos errores
                return $exception instanceof ConnectionException ||
                       ($exception instanceof RequestException && $exception->response->status() >= 500);
            })
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
        // Verificar si hay datos en caché
        if ($this->config->useCache()) {
            $cachedResults = $this->cacheService->getCachedSearchResults($query, $limit, $offset);
            if ($cachedResults !== null) {
                Log::info('Giphy: Usando resultados en caché para búsqueda', [
                    'query' => $query,
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

                return $cachedResults->toArray();
            }
        }

        // Verificar si el circuit breaker permite hacer la llamada
        if (! $this->circuitBreaker->canMakeRequest()) {
            Log::warning('Giphy: Circuit breaker impidió la llamada API', [
                'query' => $query,
            ]);

            // Si usamos fallback, intentar usar caché aunque esté expirada
            if ($this->config->useFallback()) {
                $cachedResults = $this->cacheService->getCachedSearchResults($query, $limit, $offset);
                if ($cachedResults !== null) {
                    Log::info('Giphy: Usando resultados en caché como fallback', [
                        'query' => $query,
                    ]);

                    return $cachedResults->toArray();
                }
            }

            // Si no hay datos en caché, lanzar excepción
            throw new GiphyRequestException('Servicio temporalmente no disponible');
        }

        try {
            // Intentar hacer la llamada a la API
            $response = $this->httpClient->get('/gifs/search', [
                'api_key' => $this->config->getApiKey(),
                'q' => $query,
                'limit' => $limit,
                'offset' => $offset,
                'rating' => 'g',
                'lang' => 'es',
            ]);

            // Transformar la respuesta
            $collection = $this->transformer->transformSearchResponse($response);

            // Guardar en caché
            if ($this->config->useCache()) {
                $this->cacheService->cacheSearchResults($query, $limit, $offset, $collection);
            }

            // Registrar llamada exitosa
            $this->circuitBreaker->recordSuccess();

            return $collection->toArray();
        } catch (Throwable $e) {
            return $this->handleSearchException($e, $query, $limit, $offset);
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
        // Verificar si hay datos en caché
        if ($this->config->useCache()) {
            $cachedGif = $this->cacheService->getCachedGif($id);
            if ($cachedGif !== null) {
                Log::info('Giphy: Usando GIF en caché', ['id' => $id]);

                return $cachedGif->toArray();
            }
        }

        // Verificar si el circuit breaker permite hacer la llamada
        if (! $this->circuitBreaker->canMakeRequest()) {
            Log::warning('Giphy: Circuit breaker impidió la llamada API', ['id' => $id]);

            // Si usamos fallback, intentar usar caché aunque esté expirada
            if ($this->config->useFallback()) {
                $cachedGif = $this->cacheService->getCachedGif($id);
                if ($cachedGif !== null) {
                    Log::info('Giphy: Usando GIF en caché como fallback', ['id' => $id]);

                    return $cachedGif->toArray();
                }
            }

            // Si no hay datos en caché, lanzar excepción
            throw new GiphyRequestException('Servicio temporalmente no disponible');
        }

        try {
            // Intentar hacer la llamada a la API
            $response = $this->httpClient->get('/gifs/' . $id, [
                'api_key' => $this->config->getApiKey(),
            ]);

            // Transformar la respuesta
            $gif = $this->transformer->transformGetByIdResponse($response);

            // Guardar en caché
            if ($this->config->useCache()) {
                $this->cacheService->cacheGif($id, $gif);
            }

            // Registrar llamada exitosa
            $this->circuitBreaker->recordSuccess();

            return $gif->toArray();
        } catch (Throwable $e) {
            return $this->handleGetByIdException($e, $id);
        }
    }

    /**
     * Maneja excepciones en la búsqueda de GIFs
     *
     * @throws GiphyRequestException
     * @throws GiphyNotFoundException
     * @throws GiphyResponseException
     */
    private function handleSearchException(Throwable $exception, string $query, int $limit, int $offset): array
    {
        // Registrar el error
        $this->handleException($exception, "Error al buscar GIFs: $query");

        // Registrar en circuit breaker
        $this->circuitBreaker->recordFailure($exception->getMessage());

        // Si usamos fallback, intentar usar caché aunque esté expirada
        if ($this->config->useFallback()) {
            $cachedResults = $this->cacheService->getCachedSearchResults($query, $limit, $offset);
            if ($cachedResults !== null) {
                Log::info('Giphy: Usando resultados en caché como fallback después de error', [
                    'query' => $query,
                    'error' => $exception->getMessage(),
                ]);

                return $cachedResults->toArray();
            }
        }

        // Si no hay datos en caché, propagar la excepción
        throw $this->normalizeException($exception);
    }

    /**
     * Maneja excepciones en la obtención de un GIF por ID
     *
     * @throws GiphyRequestException
     * @throws GiphyNotFoundException
     * @throws GiphyResponseException
     */
    private function handleGetByIdException(Throwable $exception, string $id): array
    {
        // Registrar el error
        $this->handleException($exception, "Error al obtener GIF por ID: $id");

        // Registrar en circuit breaker
        $this->circuitBreaker->recordFailure($exception->getMessage());

        // Si usamos fallback, intentar usar caché aunque esté expirada
        if ($this->config->useFallback()) {
            $cachedGif = $this->cacheService->getCachedGif($id);
            if ($cachedGif !== null) {
                Log::info('Giphy: Usando GIF en caché como fallback después de error', [
                    'id' => $id,
                    'error' => $exception->getMessage(),
                ]);

                return $cachedGif->toArray();
            }
        }

        // Si no hay datos en caché, propagar la excepción
        throw $this->normalizeException($exception);
    }

    /**
     * Registra información detallada sobre una excepción
     */
    private function handleException(Throwable $exception, string $context): void
    {
        // Determinar la severidad del error
        $logLevel = 'error';

        // Si es un error 404, usar nivel warning
        if ($exception instanceof GiphyNotFoundException) {
            $logLevel = 'warning';
        }

        // Si es un error de conexión, podría ser un problema temporal
        if ($exception instanceof ConnectionException) {
            $logLevel = 'warning';
        }

        // Registrar información detallada sobre el error
        Log::$logLevel("GIPHY API Error: $context", [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'class' => get_class($exception),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Normaliza la excepción para garantizar que siempre se devuelva una excepción del dominio
     */
    private function normalizeException(Throwable $exception): GiphyApiException
    {
        // Si ya es una excepción del dominio, devolverla directamente
        if ($exception instanceof GiphyApiException) {
            return $exception;
        }

        // Si es un error de conexión, convertirlo en GiphyRequestException
        if ($exception instanceof ConnectionException) {
            return new GiphyRequestException('Error de conexión con la API: ' . $exception->getMessage(), 0, $exception);
        }

        // Si es un error de HTTP, convertirlo en la excepción apropiada
        if ($exception instanceof RequestException) {
            $statusCode = $exception->getCode();

            if ($statusCode === 404) {
                return new GiphyNotFoundException('Recurso no encontrado: ' . $exception->getMessage(), 404, $exception);
            }

            if ($statusCode >= 500) {
                return new GiphyResponseException('Error en el servidor remoto: ' . $exception->getMessage(), $statusCode, $exception);
            }

            return new GiphyRequestException('Error en la petición: ' . $exception->getMessage(), $statusCode, $exception);
        }

        // Para cualquier otra excepción, convertirla en GiphyRequestException
        return new GiphyRequestException($exception->getMessage(), (int) $exception->getCode(), $exception);
    }
}
