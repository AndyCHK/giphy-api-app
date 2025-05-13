<?php

namespace App\Domain\Interfaces;

interface GiphyServiceInterface
{
    /**
     *
     * @param string $query Search query
     * @param int $limit Number of results to return
     * @param int $offset Results offset
     * @return array
     */
    public function search(string $query, int $limit = 25, int $offset = 0): array;

    /**
     *
     * @param string $id GIF ID
     * @return array|null
     */
    public function getById(string $id): ?array;

    /**
     *
     * @param int $limit Number of results to return
     * @param int $offset Results offset
     * @return array
     */
    public function getTrending(int $limit = 25, int $offset = 0): array;
} 