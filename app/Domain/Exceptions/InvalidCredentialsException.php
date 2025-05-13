<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use DomainException;

final class InvalidCredentialsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Las credenciales proporcionadas son inválidas');
    }
} 