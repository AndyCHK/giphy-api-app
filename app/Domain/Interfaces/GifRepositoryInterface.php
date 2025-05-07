<?php

namespace App\Domain\Interfaces;

interface GifRepositoryInterface
{
    /**
     * Save a GIF as favorite
     *
     * @param string $gifId
     * @param int $userId
     * @return bool
     */
    public function saveFavorite(string $gifId, int $userId): bool;

    /**
     * Remove a GIF from favorites
     *
     * @param string $gifId
     * @param int $userId
     * @return bool
     */
    public function removeFavorite(string $gifId, int $userId): bool;

    /**
     * Get user's favorite GIFs
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFavorites(int $userId, int $limit = 25, int $offset = 0): array;

    /**
     * Check if a GIF is in user's favorites
     *
     * @param string $gifId
     * @param int $userId
     * @return bool
     */
    public function isFavorite(string $gifId, int $userId): bool;
} 