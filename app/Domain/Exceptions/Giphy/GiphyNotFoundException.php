<?php

declare(strict_types=1);

namespace App\Domain\Exceptions\Giphy;

class GiphyNotFoundException extends GiphyApiException
{
    public function __construct(string $message = "GIF no encontrado", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 