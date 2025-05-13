<?php

namespace Tests\Feature;

use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiphyIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        
        $this->token = $response->json('data.token');
    }

    /**
     * @group integration
     * @group external-api
     */
    public function test_search_endpoint_returns_real_results(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/gifs/search?query=cats&limit=5');
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'images',
                    ]
                ],
                'pagination' => [
                    'count',
                    'offset',
                    'total_count'
                ]
            ]);
        
        $this->assertNotEmpty($response->json('data'));
    }

    /**
     * @group integration
     * @group external-api
     */
    public function test_show_endpoint_returns_real_gif_details(): void
    {
        $knownGifId = 'xT0xeMA0Rno1q2hDl6'; // Un ID que sabemos que existe
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/gifs/' . $knownGifId);
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'images' => [
                        'original',
                        'fixed_height'
                    ]
                ]
            ]);
        
        $this->assertEquals($knownGifId, $response->json('data.id'));
    }

    /**
     * @group integration
     * @group external-api
     */
    public function test_show_endpoint_handles_nonexistent_gif(): void
    {
        $nonexistentId = 'this_id_definitely_does_not_exist_123456789';
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/gifs/' . $nonexistentId);
        

        $this->assertTrue(
            $response->status() === 404 || $response->status() === 500,
            'La respuesta debe ser un error 404 o 500 para un GIF inexistente'
        );
    }
} 