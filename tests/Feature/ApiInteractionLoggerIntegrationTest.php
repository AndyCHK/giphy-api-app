<?php

namespace Tests\Feature;

use App\Domain\Services\ApiInteractionService;
use App\Infrastructure\Persistence\Eloquent\Models\EloquentUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Passport;
use Mockery;
use Tests\TestCase;

class ApiInteractionLoggerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $apiInteractionServiceMock;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock del servicio de interacción API
        $this->apiInteractionServiceMock = Mockery::mock(ApiInteractionService::class);
        $this->app->instance(ApiInteractionService::class, $this->apiInteractionServiceMock);

        // Crear un usuario para pruebas
        $this->user = EloquentUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Configurar los parámetros de truncado
        Config::set('api.interactions.max_content_length', 100);
        Config::set('api.interactions.truncated_message', '... [truncado]');
    }

    /** @test */
    public function it_logs_interactions_for_authenticated_requests()
    {
        // Autenticar usuario
        Passport::actingAs($this->user);

        // Configurar expectativas
        $this->apiInteractionServiceMock->shouldReceive('registerInteraction')
            ->once()
            ->withArgs(function ($userId, $service, $requestBody, $responseCode, $responseBody, $ipAddress) {
                return $userId === $this->user->id &&
                       $service === 'gifs/search' &&
                       json_decode($requestBody, true) === ['query' => 'test'] &&
                       $responseCode === 200 &&
                       $ipAddress === '127.0.0.1';
            });

        // Simular respuesta exitosa desde el controlador
        $this->mock('App\Infrastructure\Http\Controllers\GifsController', function ($mock) {
            $mock->shouldReceive('search')
                ->andReturn(response()->json(['data' => []], 200));
        });

        // Ejecutar la solicitud
        $this->getJson('/api/gifs/search?query=test');
    }

    /** @test */
    public function it_logs_interactions_for_unauthenticated_requests()
    {
        // Configurar expectativas
        $this->apiInteractionServiceMock->shouldReceive('registerInteraction')
            ->once()
            ->withArgs(function ($userId, $service, $requestBody, $responseCode, $responseBody, $ipAddress) {
                return $userId === null &&
                       $service === 'auth/login' &&
                       json_decode($requestBody, true) === ['email' => 'invalido@example.com', 'password' => '***'] &&
                       $responseCode === 401;
            });

        // Ejecutar la solicitud
        $this->postJson('/api/auth/login', [
            'email' => 'invalido@example.com',
            'password' => 'contraseñaincorrecta',
        ]);
    }

    /** @test */
    public function it_truncates_large_response_bodies()
    {
        // Autenticar usuario
        Passport::actingAs($this->user);

        // Generar una respuesta grande
        $largeResponse = ['data' => str_repeat('x', 200)];

        // Configurar expectativas
        $this->apiInteractionServiceMock->shouldReceive('registerInteraction')
            ->once()
            ->withArgs(function ($userId, $service, $requestBody, $responseCode, $responseBody, $ipAddress) {
                // Verificar que la respuesta fue truncada
                return strpos($responseBody, '... [truncado]') !== false &&
                       strlen($responseBody) <= 100 + strlen('... [truncado]');
            });

        // Simular respuesta grande desde el controlador
        $this->mock('App\Infrastructure\Http\Controllers\GifsController', function ($mock) use ($largeResponse) {
            $mock->shouldReceive('search')
                ->andReturn(response()->json($largeResponse, 200));
        });

        // Ejecutar la solicitud
        $this->getJson('/api/gifs/search?query=test');
    }

    /** @test */
    public function it_does_not_log_non_api_requests()
    {
        // No debería registrar interacciones para rutas que no sean de API
        $this->apiInteractionServiceMock->shouldNotReceive('registerInteraction');

        // Ejecutar una solicitud que no pertenece a la API
        $this->get('/login');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
