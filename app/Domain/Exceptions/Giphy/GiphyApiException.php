<?php

declare(strict_types=1);

namespace App\Domain\Exceptions\Giphy;

use Exception;

class GiphyApiException extends Exception
{
    public function __construct(string $message, int $code = 500)
    {
        parent::__construct("Error en la API de GIPHY: $message", $code);
    }
}
