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
        private readonly bool $useFallback = true,
        private readonly bool $useCache = true,
        private readonly int $errorThreshold = 5,
        private readonly int $circuitBreakerTimeout = 60,
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

    public function useFallback(): bool
    {
        return $this->useFallback;
    }

    public function useCache(): bool
    {
        return $this->useCache;
    }

    public function getErrorThreshold(): int
    {
        return $this->errorThreshold;
    }

    public function getCircuitBreakerTimeout(): int
    {
        return $this->circuitBreakerTimeout;
    }

    public static function fromConfig(): self
    {
        return new self(
            apiKey: config('services.giphy.api_key'),
            baseUrl: config('services.giphy.base_url'),
            timeout: (int) config('services.giphy.timeout', 5),
            retryAttempts: (int) config('services.giphy.retry_attempts', 3),
            retryDelay: (int) config('services.giphy.retry_delay', 1000),
            useFallback: (bool) config('services.giphy.use_fallback', true),
            useCache: (bool) config('services.giphy.use_cache', true),
            errorThreshold: (int) config('services.giphy.error_threshold', 5),
            circuitBreakerTimeout: (int) config('services.giphy.circuit_breaker_timeout', 60),
        );
    }
}
