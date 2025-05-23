<?php

namespace App\Domain\Interfaces;

interface GifRepositoryInterface
{
    /**
     * @param int|string $gifId
     * @param int $userId
     * @param string $alias
     * @return bool
     */
    public function saveFavorite(int|string $gifId, int $userId, string $alias = ''): bool;

    /**
     * @param int|string $gifId
     * @param int|string $userId
     * @return bool
     */
    public function removeFavorite(int|string $gifId, int|string $userId): bool;

    /**
     * @param int|string $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFavorites(int|string $userId, int $limit = 25, int $offset = 0): array;

    /**
     * @param int|string $gifId
     * @param int|string $userId
     * @return bool
     */
    public function isFavorite(int|string $gifId, int|string $userId): bool;
}
