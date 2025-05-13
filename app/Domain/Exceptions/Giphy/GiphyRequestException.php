<?php

declare(strict_types=1);

namespace App\Domain\Exceptions\Giphy;

class GiphyRequestException extends GiphyApiException
{
    public function __construct(string $message = "Error en la solicitud a GIPHY", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 