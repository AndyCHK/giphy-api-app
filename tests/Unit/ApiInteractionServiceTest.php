<?php

namespace Tests\Unit;

use App\Domain\Interfaces\ApiInteractionRepositoryInterface;
use App\Domain\Models\ApiInteraction;
use App\Domain\Services\ApiInteractionService;
use PHPUnit\Framework\TestCase;
use Mockery;

class ApiInteractionServiceTest extends TestCase
{
    /** @var ApiInteractionRepositoryInterface|\Mockery\MockInterface */
    private $mockRepository;
    
    /** @var ApiInteractionService */
    private $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockRepository = Mockery::mock(ApiInteractionRepositoryInterface::class);
        $this->service = new ApiInteractionService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testRegisterInteraction()
    {
        // Configure mock expectations
        $this->mockRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(ApiInteraction::class))
            ->andReturn(null);

        // Act
        $this->service->registerInteraction(
            'user-123',
            'gifs/search',
            '{"query":"cat"}',
            200,
            '{"data":[]}',
            '127.0.0.1'
        );
        
        // No necesitamos assert porque el mock verifica que se llama al método save una vez
        $this->assertTrue(true);
    }
    
    public function testRegisterInteractionWithNullValues()
    {
        // Configure mock expectations
        $this->mockRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(ApiInteraction::class))
            ->andReturn(null);

        // Act - registrar una interacción con valores nulos para comprobar que maneja correctamente
        $this->service->registerInteraction(
            null, // Usuario no autenticado
            'gifs/search',
            null, // Sin cuerpo de petición
            200,
            null, // Sin cuerpo de respuesta
            '127.0.0.1'
        );
        
        // No necesitamos assert porque el mock verifica que se llama al método save una vez
        $this->assertTrue(true);
    }
} 