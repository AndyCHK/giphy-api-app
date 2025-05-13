<?php

declare(strict_types=1);

namespace App\Domain\DTOs\Giphy;

class GifsCollectionDTO
{
    /**
     * @param GifDTO[] $gifs
     */
    public function __construct(
        public readonly array $gifs,
        public readonly int $totalCount,
        public readonly int $offset,
        public readonly int $count
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        $gifs = [];
        foreach ($data['data'] ?? [] as $gifData) {
            $gifs[] = GifDTO::fromArray($gifData);
        }

        $pagination = $data['pagination'] ?? [
            'total_count' => count($gifs),
            'count' => count($gifs),
            'offset' => 0
        ];

        return new self(
            gifs: $gifs,
            totalCount: $pagination['total_count'] ?? count($gifs),
            count: $pagination['count'] ?? count($gifs),
            offset: $pagination['offset'] ?? 0
        );
    }

    public function toArray(): array
    {
        $gifs = [];
        foreach ($this->gifs as $gif) {
            $gifs[] = $gif->toArray();
        }

        return [
            'data' => $gifs,
            'pagination' => [
                'total_count' => $this->totalCount,
                'count' => $this->count,
                'offset' => $this->offset,
            ],
        ];
    }
}
