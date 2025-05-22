<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

final class Password implements ValueObject
{
    private string $hashedValue;

    public function __construct(string $password, bool $isHashed = false)
    {
        if ($isHashed) {
            $this->hashedValue = $password;
        } else {
            $this->validate($password);
            $this->hashedValue = Hash::make($password);
        }
    }

    private function validate(string $password): void
    {
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres');
        }
    }

    public function value(): string
    {
        return $this->hashedValue;
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->hashedValue === $other->value();
    }

    public function verify(string $password): bool
    {
        if (! $this->isBcrypt($this->hashedValue)) {
            return false;
        }

        return Hash::check($password, $this->hashedValue);
    }

    private function isBcrypt(string $hash): bool
    {
        return strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0;
    }

    public function __toString(): string
    {
        return $this->hashedValue;
    }
}
