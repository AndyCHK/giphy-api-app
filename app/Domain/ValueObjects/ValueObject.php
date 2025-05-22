<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

interface ValueObject
{
    public function equals(ValueObject $other): bool;

    public function __toString(): string;
}
