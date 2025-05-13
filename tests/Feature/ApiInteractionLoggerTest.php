<?php

namespace Tests\Feature;

use App\Domain\Services\ApiInteractionService;
use App\Infrastructure\Http\Middleware\ApiInteractionLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
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
     * @test
     * @group happy
     */
    public function test_middleware_logs_api_interaction()
    {
        // Mock de ApiInteractionService
        $apiInteractionService = Mockery::mock(ApiInteractionService::class);
        $apiInteractionService->shouldReceive('registerInteraction')
            ->once()
            ->withArgs(function ($userId, $service, $requestBody, $responseCode, $responseBody, $ipAddress) {
                return $service === 'test/endpoint' &&
                       $requestBody === '{"param":"value"}' &&
                       $responseCode === 200 &&
                       $responseBody === '{"result":"success"}' &&
                       $ipAddress === '127.0.0.1';
            });

        // Mock del servicio de token
        $tokenService = Mockery::mock('App\Infrastructure\Auth\TokenService');
        $tokenService->shouldReceive('verifyToken')->andReturn(['valid' => false]);

        // Configurar los parámetros de truncado
        Config::set('api.interactions.max_content_length', 100);
        Config::set('api.interactions.truncated_message', '... [truncado]');

        // Crear middleware con mocks
        $middleware = new ApiInteractionLogger($apiInteractionService, $tokenService);

        // Crear Request
        $request = Request::create(
            'api/test/endpoint',
            'POST',
            ['param' => 'value']
        );
        $request->headers->set('X-FORWARDED-FOR', '127.0.0.1');

        // Crear Response
        $response = new Response(['result' => 'success'], 200);

        // Ejecutar middleware
        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        // Verificar que la respuesta no cambia
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(['result' => 'success'], $result->original);
    }

    /**
     * @test
     * @group happy
     */
    public function test_middleware_does_not_log_non_api_interaction()
    {
        // Mock de ApiInteractionService - no debe ser llamado
        $apiInteractionService = Mockery::mock(ApiInteractionService::class);
        $apiInteractionService->shouldNotReceive('registerInteraction');

        // Mock del servicio de token
        $tokenService = Mockery::mock('App\Infrastructure\Auth\TokenService');

        // Crear middleware con mocks
        $middleware = new ApiInteractionLogger($apiInteractionService, $tokenService);

        // Crear Request (no API)
        $request = Request::create(
            'non-api/path',
            'GET'
        );

        // Crear Response
        $response = new Response(['result' => 'non-api'], 200);

        // Ejecutar middleware
        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        // Verificar que la respuesta no cambia
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(['result' => 'non-api'], $result->original);
    }

    /**
     * @test
     * @group happy
     */
    public function test_middleware_truncates_large_content()
    {
        // Configuración para truncado
        $maxLength = 50;
        $truncatedMessage = '... [truncado]';
        Config::set('api.interactions.max_content_length', $maxLength);
        Config::set('api.interactions.truncated_message', $truncatedMessage);

        // Crear contenido grande
        $largeContent = str_repeat('X', 100);
        $expectedTruncatedContent = substr($largeContent, 0, $maxLength) . $truncatedMessage;

        // Mock de ApiInteractionService
        $apiInteractionService = Mockery::mock(ApiInteractionService::class);
        $apiInteractionService->shouldReceive('registerInteraction')
            ->once()
            ->withArgs(function ($userId, $service, $requestBody, $responseCode, $responseBody, $ipAddress) use ($expectedTruncatedContent) {
                return $responseBody === $expectedTruncatedContent;
            });

        // Mock del servicio de token
        $tokenService = Mockery::mock('App\Infrastructure\Auth\TokenService');
        $tokenService->shouldReceive('verifyToken')->andReturn(['valid' => false]);

        // Crear middleware con mocks
        $middleware = new ApiInteractionLogger($apiInteractionService, $tokenService);

        // Crear Request
        $request = Request::create('api/test', 'POST');
        $request->headers->set('X-FORWARDED-FOR', '127.0.0.1');

        // Crear Response con contenido grande
        $response = new Response($largeContent, 200);

        // Ejecutar middleware
        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });
        
        // Aserción explícita
        $this->assertEquals($response, $result, 'El middleware debe devolver la misma respuesta');
    }
} 