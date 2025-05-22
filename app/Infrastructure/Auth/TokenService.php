<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\EloquentUser;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;

class TokenService
{
    /**
     * @param User $domainUser
     * @param string $tokenName
     * @return string
     */
    public function createToken(User $domainUser, string $tokenName = 'Personal Access Token'): string
    {
        $eloquentUser = EloquentUser::where('email', (string) $domainUser->email())->first();

        if (! $eloquentUser) {
            throw new \RuntimeException('Usuario no encontrado en la base de datos');
        }

        $tokenResult = $eloquentUser->createToken($tokenName);

        return $tokenResult->accessToken;
    }

    /**
     * @param string $token
     * @return array
     */
    public function verifyToken(string $token): array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return [
                    'valid' => false,
                    'error' => 'El token no tiene un formato JWT válido',
                ];
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

            if (! $payload) {
                return [
                    'valid' => false,
                    'error' => 'No se pudo decodificar el payload del token',
                ];
            }

            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return [
                    'valid' => false,
                    'error' => 'El token ha expirado',
                ];
            }

            if (! isset($payload['jti'])) {
                return [
                    'valid' => false,
                    'error' => 'El token no tiene un identificador único (jti)',
                ];
            }

            $dbToken = Token::find($payload['jti']);

            if (! $dbToken) {
                return [
                    'valid' => false,
                    'error' => 'El token no está registrado en la base de datos',
                ];
            }

            if ($dbToken->revoked) {
                return [
                    'valid' => false,
                    'error' => 'El token ha sido revocado',
                ];
            }

            $user = EloquentUser::find($dbToken->user_id);

            if (! $user) {
                return [
                    'valid' => false,
                    'error' => 'No se encontró el usuario asociado al token',
                ];
            }

            return [
                'valid' => true,
                'user_id' => $user->id,
                'token_id' => $dbToken->id,
                'client_id' => $dbToken->client_id,
                'jti' => $payload['jti'],
            ];

        } catch (\Exception $e) {
            Log::channel('auth')->error('Error al verificar token', [
                'error' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'error' => 'Error al verificar el token: ' . $e->getMessage(),
            ];
        }
    }
}
