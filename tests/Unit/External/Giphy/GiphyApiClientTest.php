<?php

namespace Tests\Unit\External\Giphy;

use App\Domain\DTOs\Giphy\GifDTO;
use App\Domain\DTOs\Giphy\GifsCollectionDTO;
use App\Domain\Exceptions\Giphy\GiphyRequestException;
use App\Infrastructure\External\Giphy\GiphyApiClient;
use App\Infrastructure\External\Giphy\GiphyConfig;
use App\Infrastructure\External\Giphy\GiphyResponseTransformer;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class GiphyApiClientTest extends TestCase
{
    private $config;
    private $transformer;
    private $apiClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(GiphyConfig::class);
        $this->config->shouldReceive('getApiKey')->andReturn('test_api_key');
        $this->config->shouldReceive('getTimeout')->andReturn(30);
        $this->config->shouldReceive('getRetryAttempts')->andReturn(3);
        $this->config->shouldReceive('getRetryDelay')->andReturn(100);
        $this->config->shouldReceive('getBaseUrl')->andReturn('https://api.giphy.com/v1');

        $this->transformer = Mockery::mock(GiphyResponseTransformer::class);
    }

    /**
     * @test
     * @group happy
     */
    public function test_search_returns_collection_when_successful(): void
    {
        Http::fake([
            'https://api.giphy.com/v1/*' => Http::response([
                'data' => [
                    ['id' => 'gif1', 'title' => 'Test Gif 1'],
                    ['id' => 'gif2', 'title' => 'Test Gif 2'],
                ],
                'pagination' => ['total_count' => 2],
            ], 200),
        ]);

        $apiClient = new GiphyApiClient($this->config, $this->transformer);

        $collection = new GifsCollectionDTO([
            new GifDTO('gif1', 'Test Gif 1', 'url1', []),
            new GifDTO('gif2', 'Test Gif 2', 'url2', []),
        ], 2, 0, 2);

        $this->transformer->shouldReceive('transformSearchResponse')
            ->once()
            ->with(Mockery::type(Response::class))
            ->andReturn($collection);

        $result = $apiClient->search('test', 2, 0);

        $this->assertEquals($collection->toArray(), $result);
    }

    /**
     * @test
     * @group happy
     */
    public function test_get_by_id_returns_gif_when_successful(): void
    {
        Http::fake([
            'https://api.giphy.com/v1/*' => Http::response([
                'data' => [
                    'id' => 'test123',
                    'title' => 'Test Gif',
                ],
            ], 200),
        ]);

        $apiClient = new GiphyApiClient($this->config, $this->transformer);

        $gif = new GifDTO('test123', 'Test Gif', 'url', []);

        $this->transformer->shouldReceive('transformGetByIdResponse')
            ->once()
            ->with(Mockery::type(Response::class))
            ->andReturn($gif);

        $result = $apiClient->getById('test123');

        $this->assertEquals($gif->toArray(), $result);
    }

    /**
     * @test
     */
    public function test_search_throws_exception_when_http_error_occurs(): void
    {
        Http::fake([
            'https://api.giphy.com/v1/*' => Http::response(
                ['meta' => ['status' => 500, 'msg' => 'Error interno del servidor']],
                500
            ),
        ]);

        $apiClient = new GiphyApiClient($this->config, $this->transformer);

        $this->expectException(GiphyRequestException::class);

        $apiClient->search('test', 10, 0);
    }

    /**
     * @test
     */
    public function test_get_by_id_throws_exception_when_http_error_occurs(): void
    {
        Http::fake([
            'https://api.giphy.com/v1/*' => Http::response(
                ['meta' => ['status' => 404, 'msg' => 'GIF no encontrado']],
                404
            ),
        ]);

        $apiClient = new GiphyApiClient($this->config, $this->transformer);

        $this->expectException(GiphyRequestException::class);

        $apiClient->getById('nonexistent');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
