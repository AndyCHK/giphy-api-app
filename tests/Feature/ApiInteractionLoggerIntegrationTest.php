<?php

namespace Tests\Feature;

use App\Domain\Services\ApiInteractionService;
use App\Infrastructure\Http\Middleware\ApiInteractionLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;
use Mockery;

class ApiInteractionLoggerIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Prueba directamente el middleware con una petición simulada.
     */
    public function testMiddlewareHandlesRequest()
    {
        // Mock del servicio de interacciones
        $mockService = Mockery::mock(ApiInteractionService::class);
        $mockService->shouldReceive('registerInteraction')
            ->once()
            ->withArgs(function ($userId, $service, $requestBody, $responseCode, $responseBody, $ipAddress) {
                return $service === 'v1/test' && 
                       $responseCode === 200 && 
                       $ipAddress === '127.0.0.1';
            })
            ->andReturn(null);
        
        // Crear el middleware con nuestro mock
        $middleware = new ApiInteractionLogger($mockService);
        
        // Crear petición simulada a una ruta API
        $request = Request::create('/api/v1/test', 'GET', ['param' => 'value']);
        
        // Crear respuesta simulada
        $response = new Response(['result' => 'success'], 200);
        
        // Ejecutar el middleware
        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });
        
        // Verificar que la respuesta se mantiene intacta
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(['result' => 'success'], $result->original);
        
        // El mock verifica internamente que se llamó a registerInteraction con los parámetros correctos
    }
} 