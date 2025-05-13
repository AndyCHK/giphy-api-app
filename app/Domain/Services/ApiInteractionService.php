<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Interfaces\ApiInteractionRepositoryInterface;
use App\Domain\Models\ApiInteraction;
use Illuminate\Support\Str;


class ApiInteractionService
{
    public function __construct(
        private readonly ApiInteractionRepositoryInterface $apiInteractionRepository
    ) {
    }

    /**
     * @param string|null $userId 
     * @param string $service
     * @param string|null $requestBody
     * @param int $responseCode
     * @param string|null $responseBody
     * @param string $ipAddress
     */
    public function registerInteraction(
        ?string $userId,
        string $service,
        ?string $requestBody,
        int $responseCode,
        ?string $responseBody,
        string $ipAddress
    ): void {
        $apiInteraction = new ApiInteraction(
            Str::uuid()->toString(),
            $userId,
            $service,
            $requestBody,
            $responseCode,
            $responseBody,
            $ipAddress
        );

        $this->apiInteractionRepository->save($apiInteraction);
    }
} 