<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Giphy;

use App\Domain\DTOs\Giphy\GifDTO;
use App\Domain\DTOs\Giphy\GifsCollectionDTO;
use Illuminate\Support\Facades\Cache;

class GiphyCacheService
{
    // Tiempos de expiración (en segundos)
    private const SEARCH_CACHE_TTL = 3600; // 1 hora
    private const GIF_CACHE_TTL = 86400; // 24 horas
    private const TRENDING_CACHE_TTL = 1800; // 30 minutos

    /**
     * Guarda resultados de búsqueda en caché
     */
    public function cacheSearchResults(string $query, int $limit, int $offset, GifsCollectionDTO $results): void
    {
        $cacheKey = $this->getSearchCacheKey($query, $limit, $offset);
        Cache::put($cacheKey, $results, self::SEARCH_CACHE_TTL);
    }

    /**
     * Obtiene resultados de búsqueda desde la caché
     */
    public function getCachedSearchResults(string $query, int $limit, int $offset): ?GifsCollectionDTO
    {
        $cacheKey = $this->getSearchCacheKey($query, $limit, $offset);

        return Cache::get($cacheKey);
    }

    /**
     * Guarda un GIF en caché
     */
    public function cacheGif(string $id, GifDTO $gif): void
    {
        $cacheKey = $this->getGifCacheKey($id);
        Cache::put($cacheKey, $gif, self::GIF_CACHE_TTL);
    }

    /**
     * Obtiene un GIF desde la caché
     */
    public function getCachedGif(string $id): ?GifDTO
    {
        $cacheKey = $this->getGifCacheKey($id);

        return Cache::get($cacheKey);
    }

    /**
     * Genera la clave de caché para búsquedas
     */
    private function getSearchCacheKey(string $query, int $limit, int $offset): string
    {
        return "giphy_search_" . md5($query . '_' . $limit . '_' . $offset);
    }

    /**
     * Genera la clave de caché para GIFs individuales
     */
    private function getGifCacheKey(string $id): string
    {
        return "giphy_gif_" . $id;
    }
}
