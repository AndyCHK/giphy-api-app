<?php

namespace App\Domain\Services;

use App\Domain\Interfaces\GiphyIdAdapterServiceInterface;
use App\Domain\Interfaces\GiphyIdMappingRepositoryInterface;

class GiphyIdAdapterService implements GiphyIdAdapterServiceInterface
{
    private GiphyIdMappingRepositoryInterface $repository;

    public function __construct(GiphyIdMappingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function toNumericId(string $giphyId): int
    {
        return $this->repository->getOrCreateNumericId($giphyId);
    }

    public function toGiphyId(int $numericId): ?string
    {
        return $this->repository->getGiphyId($numericId);
    }
}
