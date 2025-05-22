<?php

namespace App\Domain\Interfaces;

interface GiphyIdMappingRepositoryInterface
{
    public function getNumericId(string $giphyId): ?int;

    public function createMapping(string $giphyId): int;

    public function getOrCreateNumericId(string $giphyId): int;

    public function getGiphyId(int $numericId): ?string;
}
