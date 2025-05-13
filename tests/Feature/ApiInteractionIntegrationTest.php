<?php

namespace Tests\Feature;

use App\Domain\Services\ApiInteractionService;
use App\Infrastructure\Http\Middleware\ApiAuthenticate;
use App\Domain\Interfaces\GiphyServiceInterface;
use App\Domain\DTOs\Giphy\GifsCollectionDTO;
use Mockery;
use Tests\TestCase;

class ApiInteractionIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Evitamos la autenticación real
        $this->withoutMiddleware(ApiAuthenticate::class);
        
        // Mock del servicio de Giphy para no hacer llamadas reales a la API externa
        $mockGiphyService = Mockery::mock(GiphyServiceInterface::class);
        $mockGiphyService->shouldReceive('search')
            ->andReturn(['data' => []]);
        $this->app->instance(GiphyServiceInterface::class, $mockGiphyService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testApiRequestLogsInteraction()
    {
        // Mock del servicio de registro de interacciones
        $mockService = Mockery::mock(ApiInteractionService::class);
        $mockService->shouldReceive('registerInteraction')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);
        
        // Registrar el mock en el contenedor
        $this->app->instance(ApiInteractionService::class, $mockService);
        
        // Realizar una solicitud a una ruta API real
        $response = $this->getJson('/api/gifs/search?query=test');
        
        // Verificar que la respuesta es correcta
        $response->assertStatus(200);
        
        // La verificación de que se llamó al método registerInteraction la hace Mockery
    }
} 