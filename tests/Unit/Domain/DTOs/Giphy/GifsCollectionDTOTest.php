<?php

namespace Tests\Unit\Domain\DTOs\Giphy;

use App\Domain\DTOs\Giphy\GifDTO;
use App\Domain\DTOs\Giphy\GifsCollectionDTO;
use PHPUnit\Framework\TestCase;

class GifsCollectionDTOTest extends TestCase
{
    /**
     * @test
     * @group happy
     */
    public function test_creates_collection_from_api_response(): void
    {
        // Datos de la API
        $apiResponse = [
            'data' => [
                [
                    'id' => 'gif1',
                    'title' => 'Test Gif 1',
                    'url' => 'https://giphy.com/gifs/test1',
                    'images' => []
                ],
                [
                    'id' => 'gif2',
                    'title' => 'Test Gif 2',
                    'url' => 'https://giphy.com/gifs/test2',
                    'images' => []
                ],
            ],
            'pagination' => [
                'total_count' => 100,
                'count' => 2,
                'offset' => 0
            ]
        ];

        // Crear la colección
        $collection = GifsCollectionDTO::fromApiResponse($apiResponse);

        // Verificar la colección
        $this->assertCount(2, $collection->gifs);
        $this->assertEquals(100, $collection->totalCount);
        $this->assertEquals(2, $collection->count);
        $this->assertEquals(0, $collection->offset);

        // Verificar los GIFs individuales
        $this->assertEquals('gif1', $collection->gifs[0]->id);
        $this->assertEquals('Test Gif 1', $collection->gifs[0]->title);
        $this->assertEquals('https://giphy.com/gifs/test1', $collection->gifs[0]->url);
        
        $this->assertEquals('gif2', $collection->gifs[1]->id);
        $this->assertEquals('Test Gif 2', $collection->gifs[1]->title);
        $this->assertEquals('https://giphy.com/gifs/test2', $collection->gifs[1]->url);
    }

    /**
     * @test
     * @group happy
     */
    public function test_creates_collection_from_minimal_api_response(): void
    {
        // Datos mínimos (sin paginación)
        $apiResponse = [
            'data' => [
                [
                    'id' => 'gif1',
                    'title' => 'Test Gif 1',
                    'url' => 'https://giphy.com/gifs/test1',
                    'images' => []
                ]
            ]
        ];

        // Crear la colección
        $collection = GifsCollectionDTO::fromApiResponse($apiResponse);

        // Verificar la colección
        $this->assertCount(1, $collection->gifs);
        $this->assertEquals(1, $collection->totalCount); // Debe ser igual al número de gifs
        $this->assertEquals(1, $collection->count);
        $this->assertEquals(0, $collection->offset);

        // Verificar el GIF
        $this->assertEquals('gif1', $collection->gifs[0]->id);
    }

    /**
     * @test
     * @group happy
     */
    public function test_creates_collection_from_empty_api_response(): void
    {
        // Datos vacíos
        $apiResponse = [
            'data' => []
        ];

        // Crear la colección
        $collection = GifsCollectionDTO::fromApiResponse($apiResponse);

        // Verificar la colección
        $this->assertCount(0, $collection->gifs);
        $this->assertEquals(0, $collection->totalCount);
        $this->assertEquals(0, $collection->count);
        $this->assertEquals(0, $collection->offset);
    }

    /**
     * @test
     * @group happy
     */
    public function test_converts_collection_to_array(): void
    {
        // Crear GIFs
        $gif1 = new GifDTO('gif1', 'Test Gif 1', 'url1', []);
        $gif2 = new GifDTO('gif2', 'Test Gif 2', 'url2', []);

        // Crear colección
        $collection = new GifsCollectionDTO(
            gifs: [$gif1, $gif2],
            totalCount: 100,
            count: 2,
            offset: 0
        );

        // Convertir a array
        $result = $collection->toArray();

        // Verificar el array resultante
        $expected = [
            'data' => [
                [
                    'id' => 'gif1',
                    'title' => 'Test Gif 1',
                    'url' => 'url1',
                    'images' => [],
                    'username' => null,
                    'source' => null,
                    'rating' => null,
                    'import_datetime' => null,
                ],
                [
                    'id' => 'gif2',
                    'title' => 'Test Gif 2',
                    'url' => 'url2',
                    'images' => [],
                    'username' => null,
                    'source' => null,
                    'rating' => null,
                    'import_datetime' => null,
                ]
            ],
            'pagination' => [
                'total_count' => 100,
                'count' => 2,
                'offset' => 0,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * @group happy
     */
    public function test_constructor_assigns_values_correctly(): void
    {
        // Crear GIFs
        $gif1 = new GifDTO('gif1', 'Test Gif 1', 'url1', []);
        $gif2 = new GifDTO('gif2', 'Test Gif 2', 'url2', []);
        
        // Valores específicos para la paginación
        $totalCount = 50;
        $count = 2;
        $offset = 10;

        // Crear colección con valores específicos
        $collection = new GifsCollectionDTO(
            gifs: [$gif1, $gif2],
            totalCount: $totalCount,
            count: $count,
            offset: $offset
        );

        // Verificar que los valores se asignaron correctamente
        $this->assertCount(2, $collection->gifs);
        $this->assertEquals($totalCount, $collection->totalCount);
        $this->assertEquals($count, $collection->count);
        $this->assertEquals($offset, $collection->offset);
        
        // Verificar los GIFs individuales
        $this->assertSame($gif1, $collection->gifs[0]);
        $this->assertSame($gif2, $collection->gifs[1]);
    }
} 