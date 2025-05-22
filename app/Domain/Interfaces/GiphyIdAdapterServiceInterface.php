<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

interface GiphyIdAdapterServiceInterface
{
    public function toNumericId(string $giphyId): int;

    public function toGiphyId(int $numericId): ?string;
}
