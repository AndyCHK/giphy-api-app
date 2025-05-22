<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Interfaces\ApiInteractionRepositoryInterface;

class ApiInteractionService
{
    public function __construct(
        private readonly ApiInteractionRepositoryInterface $apiInteractionRepository
    ) {

    }

    /**
     * @param string $giphyId
     * @return int
     */
    public function toNumericId(string $giphyId): int
    {
        return $this->repository->getOrCreateNumericId($giphyId);
    }

    /**
     * @param int $numericId
     * @return string|null
     */
    public function toGiphyId(int $numericId): ?string
    {
        return $this->repository->getGiphyId($numericId);
    }
}
