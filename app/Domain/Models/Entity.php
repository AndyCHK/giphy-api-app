<?php

declare(strict_types=1);

namespace App\Domain\Models;

abstract class Entity
{
    protected string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function equals(Entity $other): bool
    {
        return $this->id === $other->id;
    }
}
