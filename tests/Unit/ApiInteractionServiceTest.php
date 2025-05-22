<?php

namespace Tests\Unit;

use App\Domain\Interfaces\ApiInteractionRepositoryInterface;
use App\Domain\Services\ApiInteractionService;
use Mockery;
use PHPUnit\Framework\TestCase;

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

    /** @test */
    public function it_registers_interaction_in_repository()
    {
        // Preparar
        $userId = '123';
        $service = 'gifs/search';
        $requestBody = '{"query":"cats"}';
        $responseCode = 200;
        $responseBody = '{"success":true,"data":[]}';
        $ipAddress = '127.0.0.1';

        // Expectativas
        $this->mockRepository->shouldReceive('registerInteraction')
            ->once()
            ->with(
                $userId,
                $service,
                $requestBody,
                $responseCode,
                $responseBody,
                $ipAddress
            )
            ->andReturn(true);

        // Ejecutar
        $result = $this->service->registerInteraction(
            $userId,
            $service,
            $requestBody,
            $responseCode,
            $responseBody,
            $ipAddress
        );

        // Verificar
        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_null_user_id()
    {
        // Preparar
        $userId = null;
        $service = 'auth/login';
        $requestBody = '{"email":"test@example.com","password":"****"}';
        $responseCode = 200;
        $responseBody = '{"success":true,"data":{"token":"..."}}';
        $ipAddress = '127.0.0.1';

        // Expectativas
        $this->mockRepository->shouldReceive('registerInteraction')
            ->once()
            ->with(
                $userId,
                $service,
                $requestBody,
                $responseCode,
                $responseBody,
                $ipAddress
            )
            ->andReturn(true);

        // Ejecutar
        $result = $this->service->registerInteraction(
            $userId,
            $service,
            $requestBody,
            $responseCode,
            $responseBody,
            $ipAddress
        );

        // Verificar
        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_repository_failure()
    {
        // Preparar
        $userId = '123';
        $service = 'gifs/search';
        $requestBody = '{"query":"dogs"}';
        $responseCode = 500;
        $responseBody = '{"success":false,"message":"Internal Server Error"}';
        $ipAddress = '127.0.0.1';

        // Expectativas
        $this->mockRepository->shouldReceive('registerInteraction')
            ->once()
            ->andReturn(false);

        // Ejecutar
        $result = $this->service->registerInteraction(
            $userId,
            $service,
            $requestBody,
            $responseCode,
            $responseBody,
            $ipAddress
        );

        // Verificar
        $this->assertFalse($result);
    }
}
