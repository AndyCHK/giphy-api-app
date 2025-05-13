<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Giphy;

class GiphyConfig
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.giphy.com/v1',
        private readonly int $timeout = 5,
        private readonly int $retryAttempts = 3,
        private readonly int $retryDelay = 1000,
    ) {
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getRetryAttempts(): int
    {
        return $this->retryAttempts;
    }

    public function getRetryDelay(): int
    {
        return $this->retryDelay;
    }

    public static function fromConfig(): self
    {
        return new self(
            apiKey: config('services.giphy.api_key'),
            baseUrl: config('services.giphy.base_url'),
            timeout: (int) config('services.giphy.timeout'),
            retryAttempts: (int) config('services.giphy.retry_attempts'),
            retryDelay: (int) config('services.giphy.retry_delay'),
        );
    }
}
