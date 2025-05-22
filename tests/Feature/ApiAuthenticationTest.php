<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\EloquentUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    // Usar transacciones en lugar de recrear la base de datos en tests ejecutados desde Docker
    // La recreación puede ser lenta y causar problemas con seeds
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        EloquentUser::create([
            'id' => (string) rand(1000, 9999), // ID como string
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'roles' => ['user'],
        ]);
    }

    /** @test */
    public function user_can_login_and_receive_token()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'roles',
                    ],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertNotEmpty($response->json('data.token'));
    }

    /** @test */
    public function authenticated_user_can_access_protected_routes()
    {
        // 1. Primero hacemos login para obtener un token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.token');
        $this->assertNotEmpty($token);

        // 2. Usamos el token para acceder a una ruta protegida
        $protectedResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/gifs/search?query=cats');

        // El test debe pasar si se puede acceder a la ruta protegida
        $protectedResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function unauthenticated_requests_are_unauthorized()
    {
        $response = $this->getJson('/api/gifs/search?query=cats');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function invalid_token_fails_authentication()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalidtoken1234567890',
            'Accept' => 'application/json',
        ])->getJson('/api/gifs/search?query=cats');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }
}
