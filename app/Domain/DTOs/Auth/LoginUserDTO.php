<?php

declare(strict_types=1);

namespace App\Domain\DTOs\Auth;

final class LoginUserDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password']
        );
    }
}
