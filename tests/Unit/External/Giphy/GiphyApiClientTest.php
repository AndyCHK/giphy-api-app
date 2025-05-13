<?php

namespace Tests\Unit\External\Giphy;

use App\Domain\DTOs\Giphy\GifDTO;
use App\Domain\DTOs\Giphy\GifsCollectionDTO;
use App\Domain\Exceptions\Giphy\GifNotFoundException;
use App\Domain\Exceptions\Giphy\GiphyApiException;
use App\Infrastructure\External\Giphy\GiphyApiClient;
use App\Infrastructure\External\Giphy\GiphyConfig;
use App\Infrastructure\External\Giphy\GiphyResponseTransformer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Tests\TestCase;

class GiphyApiClientTest extends TestCase
{
    private $config;
    private $transformer;
    private $mockHandler;
    private $httpClient;
    private $apiClient;

    protected function setUp(): void
    {
        parent::setUp();

        // Mockear la configuración
        $this->config = Mockery::mock(GiphyConfig::class);
        $this->config->shouldReceive('getApiKey')->andReturn('test_api_key');

        // Mockear el transformador
        $this->transformer = Mockery::mock(GiphyResponseTransformer::class);

        // Configurar el cliente HTTP mockeado
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $this->httpClient = new Client(['handler' => $handlerStack]);

        // Crear el cliente API con dependencias mockeadas
        // Vamos a mockear también el cliente HTTP internamente
        $this->config->shouldReceive('getTimeout')->andReturn(30);
        $this->config->shouldReceive('getRetryAttempts')->andReturn(3);
        $this->config->shouldReceive('getRetryDelay')->andReturn(100);
        $this->config->shouldReceive('getBaseUrl')->andReturn('https://api.giphy.com/v1');

        $this->apiClient = new GiphyApiClient($this->config, $this->transformer);
    }

    public function test_search_returns_collection_on_successful_response(): void
    {
        $successResponse = new Response(200, [], json_encode([
            'data' => [
                ['id' => 'gif1', 'title' => 'GIF 1'],
                ['id' => 'gif2', 'title' => 'GIF 2']
            ],
            'pagination' => ['total_count' => 2, 'count' => 2, 'offset' => 0]
        ]));
        $this->mockHandler->append($successResponse);

        $collection = new GifsCollectionDTO([
            new GifDTO('gif1', 'GIF 1', 'url1', ['preview' => 'preview1']),
            new GifDTO('gif2', 'GIF 2', 'url2', ['preview' => 'preview2'])
        ], 2, 0, 2);

        $this->transformer->shouldReceive('transformSearchResponse')
            ->once()
            ->andReturn($collection);

        $result = $this->apiClient->search('test query', 10, 0);

        $this->assertEquals($collection->toArray(), $result);
    }

    public function test_search_throws_exception_on_api_error(): void
    {
        $errorResponse = new Response(400, [], json_encode([
            'meta' => [
                'status' => 400,
                'msg' => 'Bad Request'
            ]
        ]));
        $this->mockHandler->append($errorResponse);

        $this->expectException(GiphyApiException::class);

        $this->apiClient->search('test query');
    }

    public function test_search_throws_exception_on_server_error(): void
    {
        $serverErrorResponse = new Response(500, [], 'Internal Server Error');
        $this->mockHandler->append($serverErrorResponse);

        $this->expectException(GiphyApiException::class);

        $this->apiClient->search('test query');
    }

    public function test_search_throws_exception_on_connection_error(): void
    {
        // Arrange
        $request = new Request('GET', 'gifs/search');
        $exception = new RequestException('Connection error', $request);
        $this->mockHandler->append($exception);

        // Configurar expectativas
        $this->expectException(GiphyApiException::class);

        // Act
        $this->apiClient->search('test query');
    }

    public function test_find_by_id_returns_gif_on_successful_response(): void
    {
        // Arrange
        $successResponse = new Response(200, [], json_encode([
            'data' => [
                'id' => 'gif1',
                'title' => 'GIF 1',
                'images' => [
                    'original' => ['url' => 'original_url'],
                    'fixed_height' => ['url' => 'preview_url']
                ]
            ]
        ]));
        $this->mockHandler->append($successResponse);

        // Configurar expectativas del transformador
        $gif = new GifDTO('gif1', 'GIF 1', 'original_url', [
            'original' => ['url' => 'original_url'],
            'fixed_height' => ['url' => 'preview_url']
        ]);

        $this->transformer->shouldReceive('transformGifResponse')
            ->once()
            ->andReturn($gif);

        // Act
        $result = $this->apiClient->getById('gif1');

        // Assert
        $this->assertEquals($gif->toArray(), $result);
    }

    public function test_find_by_id_throws_not_found_exception_when_gif_not_exists(): void
    {
        // Arrange
        $notFoundResponse = new Response(404, [], json_encode([
            'meta' => [
                'status' => 404,
                'msg' => 'Not Found'
            ]
        ]));
        $this->mockHandler->append($notFoundResponse);

        // Configurar expectativas
        $this->expectException(GifNotFoundException::class);

        // Act
        $this->apiClient->getById('nonexistent');
    }

    public function test_find_by_id_throws_exception_on_api_error(): void
    {
        // Arrange
        $errorResponse = new Response(400, [], json_encode([
            'meta' => [
                'status' => 400,
                'msg' => 'Bad Request'
            ]
        ]));
        $this->mockHandler->append($errorResponse);

        // Configurar expectativas
        $this->expectException(GiphyApiException::class);

        // Act
        $this->apiClient->getById('gif1');
    }

    public function test_get_trending_returns_collection_on_successful_response(): void
    {
        // Arrange
        $successResponse = new Response(200, [], json_encode([
            'data' => [
                ['id' => 'trending1', 'title' => 'Trending GIF 1'],
                ['id' => 'trending2', 'title' => 'Trending GIF 2']
            ],
            'pagination' => ['total_count' => 2, 'count' => 2, 'offset' => 0]
        ]));
        $this->mockHandler->append($successResponse);

        $collection = new GifsCollectionDTO([
            new GifDTO('trending1', 'Trending GIF 1', 'url1', ['preview' => 'preview1']),
            new GifDTO('trending2', 'Trending GIF 2', 'url2', ['preview' => 'preview2'])
        ], 2, 0, 2);

        $this->transformer->shouldReceive('transformTrendingResponse')
            ->once()
            ->andReturn($collection);


        // Assert
        $this->assertEquals($collection->toArray(), $result);
    }

    public function test_get_trending_throws_exception_on_api_error(): void
    {
        // Arrange
        $errorResponse = new Response(400, [], json_encode([
            'meta' => [
                'status' => 400,
                'msg' => 'Bad Request'
            ]
        ]));
        $this->mockHandler->append($errorResponse);

        // Configurar expectativas
        $this->expectException(GiphyApiException::class);

        // Act
        $this->apiClient->getTrending();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
