<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use DomainException;

final class UserAlreadyExistsException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct("El usuario con email {$email} ya existe");
    }
}
