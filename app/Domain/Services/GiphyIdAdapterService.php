<?php

namespace App\Domain\Services;

use App\Domain\Interfaces\GiphyIdAdapterServiceInterface;

class GiphyIdAdapterService implements GiphyIdAdapterServiceInterface
{

    /**
     * @inheritDoc
     */
    public function toNumericId(string $giphyId): int
    {
        // TODO: Implement toNumericId() method.
    }

    /**
     * @inheritDoc
     */
    public function toGiphyId(int $numericId): ?string
    {
        // TODO: Implement toGiphyId() method.
    }
}
