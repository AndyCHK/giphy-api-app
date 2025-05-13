<?php

declare(strict_types=1);

namespace App\Domain\Exceptions\Giphy;

use Exception;

class GifNotFoundException extends Exception
{
    public function __construct(string $id)
    {
        parent::__construct("GIF con ID '$id' no encontrado", 404);
    }
} 