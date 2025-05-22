<?php

namespace App\Domain\Interfaces;

interface GifRepositoryInterface
{
    /**
     * @param string|int $gifId
     * @param int|string $userId
     * @param string $alias
     * @return bool
     */
    public function saveFavorite($gifId, int $userId, string $alias = ''): bool;

    /**
     * @param string|int $gifId
     * @param int|string $userId
     * @return bool
     */
    public function removeFavorite($gifId, $userId): bool;

    /**
     * @param int|string $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFavorites($userId, int $limit = 25, int $offset = 0): array;

    /**
     * @param string|int $gifId
     * @param int|string $userId
     * @return bool
     */
    public function isFavorite($gifId, $userId): bool;
}
