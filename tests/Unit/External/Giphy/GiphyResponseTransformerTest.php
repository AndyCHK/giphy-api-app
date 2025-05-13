<?php

namespace Tests\Unit\External\Giphy;

use App\Domain\DTOs\Giphy\GifDTO;
use App\Domain\DTOs\Giphy\GifsCollectionDTO;
use App\Domain\Exceptions\Giphy\GiphyNotFoundException;
use App\Domain\Exceptions\Giphy\GiphyResponseException;
use App\Infrastructure\External\Giphy\GiphyResponseTransformer;
use Illuminate\Http\Client\Response;
use Mockery;
use Tests\TestCase;

class GiphyResponseTransformerTest extends TestCase
{
    private $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new GiphyResponseTransformer();
    }

    /**
     * @test
     * @group happy
     */
    public function test_transforms_search_response_successfully(): void
    {
        $responseData = [
            'data' => [
                [
                    'id' => 'gif1',
                    'title' => 'Test Gif 1',
                    'url' => 'https://giphy.com/gifs/test1',
                    'images' => [
                        'original' => [
                            'url' => 'https://media.giphy.com/media/test1/giphy.gif'
                        ]
                    ]
                ],
                [
                    'id' => 'gif2',
                    'title' => 'Test Gif 2',
                    'url' => 'https://giphy.com/gifs/test2',
                    'images' => [
                        'original' => [
                            'url' => 'https://media.giphy.com/media/test2/giphy.gif'
                        ]
                    ]
                ]
            ],
            'pagination' => [
                'total_count' => 100,
                'count' => 2,
                'offset' => 0
            ],
            'meta' => [
                'status' => 200,
                'msg' => 'OK'
            ]
        ];

        // Mockear la respuesta
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('failed')->andReturn(false);
        $response->shouldReceive('json')->with()->andReturn($responseData);
        $response->shouldReceive('json')->with('data')->andReturn($responseData['data']);
        $response->shouldReceive('json')->with('meta.status')->andReturn(200);

        // Transformar la respuesta
        $result = $this->transformer->transformSearchResponse($response);

        // Verificar el resultado
        $this->assertInstanceOf(GifsCollectionDTO::class, $result);
        $this->assertCount(2, $result->gifs);
        $this->assertEquals(100, $result->totalCount);
        $this->assertEquals(0, $result->offset);
        $this->assertEquals(2, $result->count);
        $this->assertEquals('gif1', $result->gifs[0]->id);
        $this->assertEquals('Test Gif 2', $result->gifs[1]->title);
    }

    /**
     * @test
     * @group happy
     */
    public function test_transforms_get_by_id_response_successfully(): void
    {
        // Crear datos de respuesta
        $responseData = [
            'data' => [
                'id' => 'test123',
                'title' => 'Test Gif',
                'url' => 'https://giphy.com/gifs/test123',
                'images' => [
                    'original' => [
                        'url' => 'https://media.giphy.com/media/test123/giphy.gif'
                    ]
                ],
                'username' => 'testuser',
                'source' => 'testsource',
                'rating' => 'g',
                'import_datetime' => '2021-01-01 00:00:00'
            ],
            'meta' => [
                'status' => 200,
                'msg' => 'OK'
            ]
        ];

        // Mockear la respuesta
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('failed')->andReturn(false);
        $response->shouldReceive('json')->with()->andReturn($responseData);
        $response->shouldReceive('json')->with('data')->andReturn($responseData['data']);
        $response->shouldReceive('json')->with('meta.status')->andReturn(200);

        // Transformar la respuesta
        $result = $this->transformer->transformGetByIdResponse($response);

        // Verificar el resultado
        $this->assertInstanceOf(GifDTO::class, $result);
        $this->assertEquals('test123', $result->id);
        $this->assertEquals('Test Gif', $result->title);
        $this->assertEquals('https://giphy.com/gifs/test123', $result->url);
        $this->assertEquals('testuser', $result->username);
        $this->assertEquals('testsource', $result->source);
        $this->assertEquals('g', $result->rating);
        $this->assertEquals('2021-01-01 00:00:00', $result->importDatetime);
    }

    /**
     * @test
     */
    public function test_throws_not_found_exception_when_gif_not_found(): void
    {
        // Crear datos de respuesta
        $responseData = [
            'data' => [],
            'meta' => [
                'status' => 200,
                'msg' => 'OK'
            ]
        ];

        // Mockear la respuesta
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('failed')->andReturn(false);
        $response->shouldReceive('json')->with()->andReturn($responseData);
        $response->shouldReceive('json')->with('data')->andReturn([]);
        $response->shouldReceive('json')->with('meta.status')->andReturn(200);

        // Verificar que se lanza la excepción
        $this->expectException(GiphyNotFoundException::class);
        $this->expectExceptionMessage('No se encontró el GIF solicitado');

        // Intentar transformar la respuesta
        $this->transformer->transformGetByIdResponse($response);
    }

    /**
     * @test
     */
    public function test_throws_exception_on_failed_response(): void
    {
        // Mockear la respuesta con error
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('failed')->andReturn(true);
        $response->shouldReceive('status')->andReturn(500);
        $response->shouldReceive('json')->with('meta.msg')->andReturn('Error del servidor');

        // Verificar que se lanza la excepción
        $this->expectException(GiphyResponseException::class);
        $this->expectExceptionMessage('Error del servidor');

        // Intentar transformar la respuesta
        $this->transformer->transformSearchResponse($response);
    }

    /**
     * @test
     */
    public function test_throws_not_found_exception_on_404_response(): void
    {
        // Mockear la respuesta con error 404
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('failed')->andReturn(true);
        $response->shouldReceive('status')->andReturn(404);
        $response->shouldReceive('json')->with('meta.msg')->andReturn('GIF no encontrado');

        // Verificar que se lanza la excepción
        $this->expectException(GiphyNotFoundException::class);
        $this->expectExceptionMessage('GIF no encontrado');

        // Intentar transformar la respuesta
        $this->transformer->transformSearchResponse($response);
    }

    /**
     * @test
     */
    public function test_trending_transform_uses_search_transform(): void
    {
        // Crear datos de respuesta
        $responseData = [
            'data' => [
                [
                    'id' => 'trending1',
                    'title' => 'Trending Gif 1',
                    'url' => 'https://giphy.com/gifs/trending1',
                    'images' => []
                ]
            ],
            'pagination' => [
                'total_count' => 50,
                'count' => 1,
                'offset' => 0
            ],
            'meta' => [
                'status' => 200,
                'msg' => 'OK'
            ]
        ];

        // Mockear la respuesta
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('failed')->andReturn(false);
        $response->shouldReceive('json')->with()->andReturn($responseData);
        $response->shouldReceive('json')->with('data')->andReturn($responseData['data']);
        $response->shouldReceive('json')->with('meta.status')->andReturn(200);

        // Transformar la respuesta
        $result = $this->transformer->transformTrendingResponse($response);

        // Verificar el resultado
        $this->assertInstanceOf(GifsCollectionDTO::class, $result);
        $this->assertCount(1, $result->gifs);
        $this->assertEquals('trending1', $result->gifs[0]->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 