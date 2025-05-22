<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

interface GiphyIdAdapterServiceInterface
{
    /**
     * ID alfanumérico de Giphy a ID numérico interno
     * 
     * @param string $giphyId
     * @return int
     */
    public function toNumericId(string $giphyId): int;
    
    /**
     * ID numérico interno a su correspondiente ID alfanumérico de Giphy
     * 
     * @param int $numericId
     * @return string|null
     */
    public function toGiphyId(int $numericId): ?string;
}
