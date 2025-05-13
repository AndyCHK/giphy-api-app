<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\Models\User;
use App\Domain\ValueObjects\Email;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function save(User $user): void;
    public function update(User $user): void;
    public function delete(string $id): void;
} 