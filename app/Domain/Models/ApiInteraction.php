<?php

declare(strict_types=1);

namespace App\Domain\Models;

use DateTime;

class ApiInteraction extends Entity
{
    private ?string $userId;
    private string $service;
    private ?string $requestBody;
    private int $responseCode;
    private ?string $responseBody;
    private string $ipAddress;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        string $id,
        ?string $userId,
        string $service,
        ?string $requestBody,
        int $responseCode,
        ?string $responseBody,
        string $ipAddress,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        parent::__construct($id);
        $this->userId = $userId;
        $this->service = $service;
        $this->requestBody = $requestBody;
        $this->responseCode = $responseCode;
        $this->responseBody = $responseBody;
        $this->ipAddress = $ipAddress;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function service(): string
    {
        return $this->service;
    }

    public function requestBody(): ?string
    {
        return $this->requestBody;
    }

    public function responseCode(): int
    {
        return $this->responseCode;
    }

    public function responseBody(): ?string
    {
        return $this->responseBody;
    }

    public function ipAddress(): string
    {
        return $this->ipAddress;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTime
    {
        return $this->updatedAt;
    }
} 