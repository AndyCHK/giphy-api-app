<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Password;
use DateTime;

class User extends Entity
{
    private Email $email;
    private Password $password;
    private string $name;
    private array $roles;
    private ?DateTime $emailVerifiedAt;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        string $id,
        Email $email,
        Password $password,
        string $name,
        array $roles = [],
        ?DateTime $emailVerifiedAt = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        parent::__construct($id);
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->roles = $roles;
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function roles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function verifyPassword(string $password): bool
    {
        return $this->password->verify($password);
    }

    
    public function getPasswordHash(): string
    {
        return (string) $this->password;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function emailVerifiedAt(): ?DateTime
    {
        return $this->emailVerifiedAt;
    }

    public function verifyEmail(): void
    {
        $this->emailVerifiedAt = new DateTime();
        $this->updatedAt = new DateTime();
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