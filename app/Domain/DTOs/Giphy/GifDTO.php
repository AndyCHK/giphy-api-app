<?php

declare(strict_types=1);

namespace App\Domain\DTOs\Giphy;

class GifDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $url,
        public readonly array $images,
        public readonly ?string $username = null,
        public readonly ?string $source = null,
        public readonly ?string $rating = null,
        public readonly ?string $importDatetime = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            title: $data['title'] ?? '',
            url: $data['url'] ?? '',
            images: $data['images'] ?? [],
            username: $data['username'] ?? null,
            source: $data['source'] ?? null,
            rating: $data['rating'] ?? null,
            importDatetime: $data['import_datetime'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'images' => $this->images,
            'username' => $this->username,
            'source' => $this->source,
            'rating' => $this->rating,
            'import_datetime' => $this->importDatetime,
        ];
    }
}
