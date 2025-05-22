<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Domain\Services\ApiInteractionService;
use App\Infrastructure\Auth\TokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiInteractionLogger
{
    public function __construct(
        private readonly ApiInteractionService $apiInteractionService,
        private readonly TokenService $tokenService
    ) {
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('api/*')) {
            $this->logInteraction($request, $response);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    protected function logInteraction(Request $request, Response $response): void
    {
        try {
            $path = $request->path();
            $service = str_replace('api/', '', $path);

            $userId = $this->getUserId($request);

            $requestBody = $this->sanitizeContent(json_encode($request->all()));
            $responseCode = $response->getStatusCode();
            $responseBody = $this->sanitizeContent($response->getContent());
            $ipAddress = $request->ip();

            $this->apiInteractionService->registerInteraction(
                $userId,
                $service,
                $requestBody,
                $responseCode,
                $responseBody,
                $ipAddress
            );
        } catch (\Throwable $e) {
            Log::error('Error al registrar interacción API', [
                'message' => $e->getMessage(),
                'path' => $request->path(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * @param Request $request
     * @return string|null
     */
    private function getUserId(Request $request): ?string
    {
        $userId = Auth::id();
        if (! empty($userId)) {
            return $userId;
        }

        $bearerToken = $request->bearerToken();
        if (empty($bearerToken)) {
            return null;
        }

        $tokenInfo = $this->tokenService->verifyToken($bearerToken);
        if (! $tokenInfo['valid']) {
            return null;
        }

        return $tokenInfo['user_id'];
    }

    /**
     * @param string|null $content
     * @return string|null
     */
    private function sanitizeContent(?string $content): ?string
    {
        if ($content === null) {
            return null;
        }

        $maxLength = Config::get('api.interactions.max_content_length', 10000);
        $truncatedMessage = Config::get('api.interactions.truncated_message', '... [contenido truncado]');

        if (strlen($content) > $maxLength) {
            return substr($content, 0, $maxLength) . $truncatedMessage;
        }

        return $content;
    }
}
