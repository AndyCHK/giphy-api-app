<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Giphy;

use App\Domain\DTOs\Giphy\GifDTO;
use App\Domain\DTOs\Giphy\GifsCollectionDTO;
use App\Domain\Exceptions\Giphy\GiphyNotFoundException;
use App\Domain\Exceptions\Giphy\GiphyResponseException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class GiphyResponseTransformer
{
    /**
     * @throws GiphyResponseException
     * @throws GiphyNotFoundException
     */
    public function transformSearchResponse(Response $response): GifsCollectionDTO
    {
        $this->validateResponse($response);

        $data = $response->json();

        if (! isset($data['data']) || ! is_array($data['data'])) {
            $this->logInvalidResponse('Falta el campo "data" o no es un array', $data);

            throw new GiphyResponseException('Formato de respuesta inválido: falta el campo "data" o no es un array');
        }

        if (empty($data['data'])) {
            Log::info('Giphy: Búsqueda sin resultados');

            return new GifsCollectionDTO([], 0, 0, 0);
        }

        if (! isset($data['pagination']) || ! is_array($data['pagination'])) {
            $this->logInvalidResponse('Falta el campo "pagination" o no es un array', $data);
            $data['pagination'] = [
                'total_count' => count($data['data']),
                'count' => count($data['data']),
                'offset' => 0,
            ];
            Log::warning('Giphy: Se creó información de paginación predeterminada');
        }

        return GifsCollectionDTO::fromApiResponse($data);
    }

    /**
     * @throws GiphyResponseException
     * @throws GiphyNotFoundException
     */
    public function transformGetByIdResponse(Response $response): GifDTO
    {
        $this->validateResponse($response);

        $data = $response->json();

        if (! isset($data['data']) || ! is_array($data['data'])) {
            $this->logInvalidResponse('Falta el campo "data" o no es un array', $data);

            throw new GiphyResponseException('Formato de respuesta inválido: falta el campo "data" o no es un array');
        }

        if (empty($data['data'])) {
            Log::warning('Giphy: GIF no encontrado', ['response' => $data]);

            throw new GiphyNotFoundException("No se encontró el GIF solicitado");
        }

        $requiredFields = ['id', 'title', 'images'];
        foreach ($requiredFields as $field) {
            if (! isset($data['data'][$field])) {
                $this->logInvalidResponse("Falta el campo requerido: $field", $data['data']);

                throw new GiphyResponseException("Formato de respuesta inválido: falta el campo requerido '$field'");
            }
        }

        return GifDTO::fromArray($data['data']);
    }

    /**
     * @throws GiphyResponseException
     * @throws GiphyNotFoundException
     */
    public function transformTrendingResponse(Response $response): GifsCollectionDTO
    {
        return $this->transformSearchResponse($response);
    }

    /**
     * @throws GiphyResponseException
     * @throws GiphyNotFoundException
     */
    private function validateResponse(Response $response): void
    {
        if ($response->failed()) {
            $statusCode = $response->status();
            $errorMessage = $this->getErrorMessage($response);

            Log::warning('Giphy: Error en respuesta HTTP', [
                'status' => $statusCode,
                'message' => $errorMessage,
                'body' => $this->truncateResponseBody($response->body()),
                'headers' => $response->headers(),
            ]);

            if ($statusCode === 404) {
                throw new GiphyNotFoundException($errorMessage, $statusCode);
            }

            if ($statusCode === 429) {
                throw new GiphyResponseException("Límite de peticiones excedido: $errorMessage", $statusCode);
            }

            if ($statusCode >= 500) {
                throw new GiphyResponseException("Error en el servidor de Giphy: $errorMessage", $statusCode);
            }

            throw new GiphyResponseException("Error HTTP $statusCode: $errorMessage", $statusCode);
        }

        try {
            $jsonData = $response->json();
        } catch (\Exception $e) {
            Log::error('Giphy: Respuesta no es JSON válido', [
                'body' => $this->truncateResponseBody($response->body()),
                'error' => $e->getMessage(),
            ]);

            throw new GiphyResponseException('Respuesta no es JSON válido: ' . $e->getMessage());
        }

        // Validar meta status si existe
        if (isset($jsonData['meta']) && isset($jsonData['meta']['status'])) {
            $apiStatus = $jsonData['meta']['status'];
            if ($apiStatus !== 200) {
                $apiMessage = $jsonData['meta']['msg'] ?? "Código de estado $apiStatus";
                $this->logInvalidResponse("Error en API de Giphy: $apiMessage", $jsonData);

                throw new GiphyResponseException("Error en API de Giphy: $apiMessage", $apiStatus);
            }
        }
    }

    /**
     * Extrae el mensaje de error de una respuesta fallida
     */
    private function getErrorMessage(Response $response): string
    {
        // Intentar obtener mensaje de error del objeto meta
        try {
            $jsonData = $response->json();
            if (isset($jsonData['meta']) && isset($jsonData['meta']['msg'])) {
                return $jsonData['meta']['msg'];
            }
        } catch (\Exception $e) {
            // Falló la decodificación JSON, ignorar y usar el mensaje predeterminado
        }

        return "Error HTTP " . $response->status();
    }

    /**
     * Trunca el cuerpo de la respuesta para el log (evitar respuestas enormes)
     */
    private function truncateResponseBody(string $body, int $maxLength = 500): string
    {
        if (strlen($body) <= $maxLength) {
            return $body;
        }

        return substr($body, 0, $maxLength) . '... [truncado]';
    }

    /**
     * Registra una respuesta inválida con información detallada
     */
    private function logInvalidResponse(string $message, array $data): void
    {
        Log::warning('Giphy: Respuesta con formato inválido', [
            'message' => $message,
            'data' => json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR),
        ]);
    }
}
