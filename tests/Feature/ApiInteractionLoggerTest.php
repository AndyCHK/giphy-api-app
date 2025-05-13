<?php

namespace Tests\Feature;

use App\Domain\Services\ApiInteractionService;
use App\Infrastructure\Http\Middleware\ApiInteractionLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Tests\TestCase;

class ApiInteractionLoggerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test directo del middleware sin depender de la integración con las rutas
     */
    public function testMiddlewareLogsApiInteraction()
    {
        // Mock del servicio
        $mockService = Mockery::mock(ApiInteractionService::class);
        $mockService->shouldReceive('registerInteraction')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);
        
        // Crear el middleware con el mock del servicio
        $middleware = new ApiInteractionLogger($mockService);
        
        // Crear request y response de prueba
        $request = Request::create('/api/test', 'GET', ['query' => 'test']);
        $response = new Response(['data' => []], 200);
        
        // Ejecutar el middleware directamente
        $resultResponse = $middleware->handle($request, function () use ($response) {
            return $response;
        });
        
        // Verificar que la respuesta es la esperada
        $this->assertEquals(200, $resultResponse->getStatusCode());
        // El mock verifica internamente que se llamó a registerInteraction
    }

    /**
     * Verificar que el middleware no registra rutas que no sean de la API
     */
    public function testMiddlewareDoesNotLogNonApiInteraction()
    {
        // Mock del servicio - no debe llamarse para rutas que no sean de API
        $mockService = Mockery::mock(ApiInteractionService::class);
        $mockService->shouldNotReceive('registerInteraction');
        
        // Crear el middleware con el mock del servicio
        $middleware = new ApiInteractionLogger($mockService);
        
        // Crear request y response de prueba para una ruta no API
        $request = Request::create('/non-api-route', 'GET');
        $response = new Response(['data' => []], 200);
        
        // Ejecutar el middleware directamente
        $resultResponse = $middleware->handle($request, function () use ($response) {
            return $response;
        });
        
        // Verificar que la respuesta es la esperada
        $this->assertEquals(200, $resultResponse->getStatusCode());
        // El mock verifica internamente que no se llamó a registerInteraction
    }
} 