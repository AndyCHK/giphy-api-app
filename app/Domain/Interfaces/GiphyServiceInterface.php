<?php

namespace App\Domain\Interfaces;

interface GiphyServiceInterface
{
    /**
     * @param string $query 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function search(string $query, int $limit = 25, int $offset = 0): array;

    /**
     * @param string $id GIF ID
     * @return array|null
     */
    public function getById(string $id): ?array;

} 