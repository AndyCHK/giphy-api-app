<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Interfaces\GiphyIdMappingRepositoryInterface;
use App\Domain\Models\GiphyIdMapping;

class EloquentGiphyIdMappingRepository implements GiphyIdMappingRepositoryInterface
{
    //TODO Rx revisar
    /*
    public function getNumericId(string $giphyId): ?int
    {
        $mapping = GiphyIdMapping::where('giphy_id', $giphyId)->first();
        return $mapping?->id ?? null;
    }

    public function createMapping(string $giphyId): int
    {
        $mapping = GiphyIdMapping::create(['giphy_id' => $giphyId]);
        return $mapping->id;
    }

    public function getOrCreateNumericId(string $giphyId): int
    {
        //return $this->getNumericId($giphyId) ?? $this->createMapping($giphyId);
        $mapping = GiphyIdMapping::firstOrCreate(['giphy_id' => $giphyId]);
        return $mapping->id;
    }

    public function getGiphyId(int $numericId): ?string
    {
        $mapping = GiphyIdMapping::find($numericId);
        //return $mapping ? $mapping->giphy_id : null;
        return $mapping?->giphy_id ?? null;
    }
    */

    public function getNumericId(string $giphyId): ?int
    {
        $mapping = GiphyIdMapping::where('giphy_id', $giphyId)->first();
        return $mapping ? $mapping->id : null;
    }

    public function createMapping(string $giphyId): int
    {
        $mapping = GiphyIdMapping::create(['giphy_id' => $giphyId]);
        return $mapping->id;
    }

    public function getOrCreateNumericId(string $giphyId): int
    {
        $mapping = GiphyIdMapping::firstOrCreate(['giphy_id' => $giphyId]);
        return $mapping->id;
    }

    public function getGiphyId(int $numericId): ?string
    {
        $mapping = GiphyIdMapping::find($numericId);
        return $mapping ? $mapping->giphy_id : null;
    }
}
