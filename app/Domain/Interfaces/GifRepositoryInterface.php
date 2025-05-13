<?php

namespace App\Domain\Interfaces;

interface GifRepositoryInterface
{
    /**
     * @param string $gifId
     * @param int|string $userId
     * @param string $alias
     * @return bool
     */
    public function saveFavorite(string $gifId, $userId, string $alias = ''): bool;

    /**
     * @param string $gifId
     * @param int|string $userId
     * @return bool
     */
    public function removeFavorite(string $gifId, $userId): bool;

    /**
     * @param int|string $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFavorites($userId, int $limit = 25, int $offset = 0): array;

    /**
     * @param string $gifId
     * @param int|string $userId
     * @return bool
     */
    public function isFavorite(string $gifId, $userId): bool;
} 