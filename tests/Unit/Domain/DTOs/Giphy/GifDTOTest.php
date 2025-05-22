<?php

namespace Tests\Unit\Domain\DTOs\Giphy;

use App\Domain\DTOs\Giphy\GifDTO;
use PHPUnit\Framework\TestCase;

class GifDTOTest extends TestCase
{
    /**
     * @test
     * @group happy
     */
    public function test_creates_gif_dto_from_array(): void
    {
        // Datos de entrada completos
        $data = [
            'id' => 'test123',
            'title' => 'Test Gif',
            'url' => 'https://giphy.com/gifs/test123',
            'images' => [
                'original' => [
                    'url' => 'https://media.giphy.com/media/test123/giphy.gif',
                ],
            ],
            'username' => 'testuser',
            'source' => 'testsource',
            'rating' => 'g',
            'import_datetime' => '2021-01-01 00:00:00',
        ];

        // Crear el DTO
        $gif = GifDTO::fromArray($data);

        // Verificar los datos
        $this->assertEquals('test123', $gif->id);
        $this->assertEquals('Test Gif', $gif->title);
        $this->assertEquals('https://giphy.com/gifs/test123', $gif->url);
        $this->assertEquals($data['images'], $gif->images);
        $this->assertEquals('testuser', $gif->username);
        $this->assertEquals('testsource', $gif->source);
        $this->assertEquals('g', $gif->rating);
        $this->assertEquals('2021-01-01 00:00:00', $gif->importDatetime);
    }

    /**
     * @test
     * @group happy
     */
    public function test_creates_gif_dto_from_minimal_array(): void
    {
        $data = [
            'id' => 'test123',
            'title' => 'Test Gif',
            'url' => 'https://giphy.com/gifs/test123',
            'images' => [],
        ];

        $gif = GifDTO::fromArray($data);

        $this->assertEquals('test123', $gif->id);
        $this->assertEquals('Test Gif', $gif->title);
        $this->assertEquals('https://giphy.com/gifs/test123', $gif->url);
        $this->assertEquals([], $gif->images);

        $this->assertNull($gif->username);
        $this->assertNull($gif->source);
        $this->assertNull($gif->rating);
        $this->assertNull($gif->importDatetime);
    }

    /**
     * @test
     * @group happy
     */
    public function test_creates_gif_dto_from_empty_array(): void
    {
        $data = [];

        $gif = GifDTO::fromArray($data);

        $this->assertEquals('', $gif->id);
        $this->assertEquals('', $gif->title);
        $this->assertEquals('', $gif->url);
        $this->assertEquals([], $gif->images);
        $this->assertNull($gif->username);
        $this->assertNull($gif->source);
        $this->assertNull($gif->rating);
        $this->assertNull($gif->importDatetime);
    }

    /**
     * @test
     * @group happy
     */
    public function test_converts_gif_dto_to_array(): void
    {
        $gif = new GifDTO(
            id: 'test123',
            title: 'Test Gif',
            url: 'https://giphy.com/gifs/test123',
            images: [
                'original' => [
                    'url' => 'https://media.giphy.com/media/test123/giphy.gif',
                ],
            ],
            username: 'testuser',
            source: 'testsource',
            rating: 'g',
            importDatetime: '2021-01-01 00:00:00'
        );

        $array = $gif->toArray();

        $expected = [
            'id' => 'test123',
            'title' => 'Test Gif',
            'url' => 'https://giphy.com/gifs/test123',
            'images' => [
                'original' => [
                    'url' => 'https://media.giphy.com/media/test123/giphy.gif',
                ],
            ],
            'username' => 'testuser',
            'source' => 'testsource',
            'rating' => 'g',
            'import_datetime' => '2021-01-01 00:00:00',
        ];

        $this->assertEquals($expected, $array);
    }
}
