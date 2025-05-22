<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Giphy;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GiphyCircuitBreaker
{
    private const CACHE_KEY_PREFIX = 'giphy_circuit_breaker_';
    private const CACHE_KEY_ERRORS = 'error_count';
    private const CACHE_KEY_LAST_ERROR = 'last_error_time';
    private const CACHE_KEY_STATE = 'state';

    // Estados posibles
    private const STATE_CLOSED = 'closed';       // Funcionando normalmente
    private const STATE_OPEN = 'open';           // No realizar llamadas
    private const STATE_HALF_OPEN = 'half_open'; // Probar si la API se ha recuperado

    private GiphyConfig $config;

    public function __construct(GiphyConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Verifica si se puede realizar la llamada a la API
     */
    public function canMakeRequest(): bool
    {
        $state = $this->getState();

        switch ($state) {
            case self::STATE_CLOSED:
                return true;

            case self::STATE_OPEN:
                // Verificar si ha pasado suficiente tiempo para intentar de nuevo
                $lastErrorTime = $this->getLastErrorTime();
                $now = time();

                if (($now - $lastErrorTime) > $this->config->getCircuitBreakerTimeout()) {
                    $this->setState(self::STATE_HALF_OPEN);
                    Log::info('Giphy CircuitBreaker: Cambiando a estado HALF_OPEN para intentar nuevamente');

                    return true;
                }

                return false;

            case self::STATE_HALF_OPEN:
                return true;

            default:
                return true;
        }
    }

    /**
     * Registra un error en una llamada a la API
     */
    public function recordFailure(string $errorMessage): void
    {
        $currentState = $this->getState();
        $errorCount = $this->getErrorCount() + 1;

        // Registrar el error actual
        $this->setErrorCount($errorCount);
        $this->setLastErrorTime(time());

        // Registrar en el log
        Log::warning("Giphy CircuitBreaker: Error registrado [$errorCount/{$this->config->getErrorThreshold()}]", [
            'message' => $errorMessage,
            'state' => $currentState,
        ]);

        // Si estamos en estado semi-abierto, cualquier error vuelve a abrir el circuito
        if ($currentState === self::STATE_HALF_OPEN) {
            $this->setState(self::STATE_OPEN);
            Log::warning('Giphy CircuitBreaker: Error en estado HALF_OPEN, cambiando a OPEN');

            return;
        }

        // Si superamos el umbral de errores, abrir el circuito
        if ($errorCount >= $this->config->getErrorThreshold()) {
            $this->setState(self::STATE_OPEN);
            Log::warning('Giphy CircuitBreaker: Umbral de errores superado, cambiando a OPEN');
        }
    }

    /**
     * Registra una llamada exitosa a la API
     */
    public function recordSuccess(): void
    {
        $currentState = $this->getState();

        // Si estábamos en estado semi-abierto y la llamada fue exitosa, cerrar el circuito
        if ($currentState === self::STATE_HALF_OPEN) {
            $this->setState(self::STATE_CLOSED);
            $this->setErrorCount(0);
            Log::info('Giphy CircuitBreaker: Llamada exitosa en estado HALF_OPEN, cambiando a CLOSED');
        }

        // Si estábamos en estado cerrado, resetear el contador de errores
        if ($currentState === self::STATE_CLOSED) {
            $this->setErrorCount(0);
        }
    }

    /**
     * Obtiene el estado actual del circuit breaker
     */
    private function getState(): string
    {
        return Cache::get($this->getCacheKey(self::CACHE_KEY_STATE), self::STATE_CLOSED);
    }

    /**
     * Establece el estado del circuit breaker
     */
    private function setState(string $state): void
    {
        Cache::put($this->getCacheKey(self::CACHE_KEY_STATE), $state, 3600 * 24);
    }

    /**
     * Obtiene el contador de errores actual
     */
    private function getErrorCount(): int
    {
        return (int) Cache::get($this->getCacheKey(self::CACHE_KEY_ERRORS), 0);
    }

    /**
     * Establece el contador de errores
     */
    private function setErrorCount(int $count): void
    {
        Cache::put($this->getCacheKey(self::CACHE_KEY_ERRORS), $count, 3600 * 24);
    }

    /**
     * Obtiene el timestamp del último error
     */
    private function getLastErrorTime(): int
    {
        return (int) Cache::get($this->getCacheKey(self::CACHE_KEY_LAST_ERROR), 0);
    }

    /**
     * Establece el timestamp del último error
     */
    private function setLastErrorTime(int $timestamp): void
    {
        Cache::put($this->getCacheKey(self::CACHE_KEY_LAST_ERROR), $timestamp, 3600 * 24);
    }

    /**
     * Genera una clave de caché
     */
    private function getCacheKey(string $type): string
    {
        return self::CACHE_KEY_PREFIX . $type;
    }
}
