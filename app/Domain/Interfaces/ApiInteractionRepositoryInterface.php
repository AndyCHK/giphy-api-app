<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\Models\ApiInteraction;

interface ApiInteractionRepositoryInterface
{
    public function save(ApiInteraction $apiInteraction): void;
    public function findById(string $id): ?ApiInteraction;
    public function findByUserId(string $userId): array;
    public function findByService(string $service): array;
    public function findByResponseCode(int $responseCode): array;
} 