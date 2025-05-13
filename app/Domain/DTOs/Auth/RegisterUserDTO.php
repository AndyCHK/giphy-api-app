<?php

declare(strict_types=1);

namespace App\Domain\DTOs\Auth;

final class RegisterUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly array $roles = []
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            roles: $data['roles'] ?? []
        );
    }
} 