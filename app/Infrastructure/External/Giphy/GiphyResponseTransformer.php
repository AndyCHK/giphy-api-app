<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Giphy;

use App\Domain\DTOs\Giphy\GifDTO;
use App\Domain\DTOs\Giphy\GifsCollectionDTO;
use App\Domain\Exceptions\Giphy\GiphyNotFoundException;
use App\Domain\Exceptions\Giphy\GiphyResponseException;
use Illuminate\Http\Client\Response;

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

        if (empty($data['data'])) {
            throw new GiphyNotFoundException("No se encontró el GIF solicitado");
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
            $errorMessage = $response->json('meta.msg') ?? "Error HTTP $statusCode";

            if ($statusCode === 404) {
                throw new GiphyNotFoundException($errorMessage);
            }

            throw new GiphyResponseException($errorMessage, $statusCode);
        }

        if (!$response->json('data') && !$response->json('meta.status') === 200) {
            throw new GiphyResponseException("Formato de respuesta inválido");
        }
    }
}
