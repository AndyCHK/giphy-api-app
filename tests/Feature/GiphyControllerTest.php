<?php

namespace Tests\Feature;

use App\Domain\Interfaces\GiphyServiceInterface;
use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GiphyControllerTest extends TestCase
{
    use RefreshDatabase;

    private $giphyService;
    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Mockear el servicio de GIPHY
        $this->giphyService = Mockery::mock(GiphyServiceInterface::class);
        $this->app->instance(GiphyServiceInterface::class, $this->giphyService);

        // Crear un usuario y generar token
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Generamos un token real para las pruebas
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->token = $response->json('data.token');
    }

    public function test_search_endpoint_returns_success_with_results(): void
    {
        // Arrange
        $mockResponse = [
            'data' => [
                [
                    'id' => 'gif1',
                    'title' => 'Test GIF 1',
                    'url' => 'https://example.com/gif1.gif',
                    'images' => ['original' => ['url' => 'https://example.com/gif1.gif']],
                ],
                [
                    'id' => 'gif2',
                    'title' => 'Test GIF 2',
                    'url' => 'https://example.com/gif2.gif',
                    'images' => ['original' => ['url' => 'https://example.com/gif2.gif']],
                ],
            ],
            'pagination' => [
                'count' => 2,
                'offset' => 0,
                'total_count' => 2,
            ],
        ];

        $this->giphyService->shouldReceive('search')
            ->with('cats', 10, 0)
            ->once()
            ->andReturn($mockResponse);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/gifs/search?query=cats&limit=10&offset=0');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse['data'],
                'pagination' => $mockResponse['pagination'],
            ]);
    }

    public function test_search_endpoint_validates_input(): void
    {
        // Act - Enviar solicitud sin query
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/gifs/search');

        // Assert - Verificar que se devuelve un error de validación
        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['query'],
            ]);
    }

    public function test_search_endpoint_handles_api_errors(): void
    {
        // Arrange
        $this->giphyService->shouldReceive('search')
            ->with('error', 10, 0)
            ->once()
            ->andThrow(new \Exception('API Error'));

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/gifs/search?query=error&limit=10&offset=0');

        // Assert
        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Error al buscar GIFs',
            ]);
    }

    public function test_show_endpoint_returns_gif_details(): void
    {
        // Arrange
        $mockResponse = [
            'id' => 'gif1',
            'title' => 'Test GIF 1',
            'url' => 'https://example.com/gif1.gif',
            'images' => [
                'original' => ['url' => 'https://example.com/gif1.gif'],
                'fixed_height' => ['url' => 'https://example.com/gif1_fixed.gif'],
            ],
        ];

        $this->giphyService->shouldReceive('getById')
            ->with('gif1')
            ->once()
            ->andReturn($mockResponse);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/gifs/gif1');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse,
            ]);
    }

    public function test_show_endpoint_returns_not_found_for_nonexistent_gif(): void
    {
        // Arrange
        $this->giphyService->shouldReceive('getById')
            ->with('nonexistent')
            ->once()
            ->andReturn(null);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/gifs/nonexistent');

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'GIF no encontrado',
            ]);
    }

    public function test_show_endpoint_handles_api_errors(): void
    {
        // Arrange
        $this->giphyService->shouldReceive('getById')
            ->with('error')
            ->once()
            ->andThrow(new \Exception('API Error'));

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/gifs/error');

        // Assert
        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Error al obtener GIF',
            ]);
    }

    public function test_endpoints_require_authentication(): void
    {
        $searchResponse = $this->getJson('/api/gifs/search?query=cats');
        $showResponse = $this->getJson('/api/gifs/gif1');

        $searchResponse->assertStatus(401);
        $showResponse->assertStatus(401);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
