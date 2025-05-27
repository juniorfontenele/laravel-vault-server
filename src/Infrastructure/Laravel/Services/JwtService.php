<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use JuniorFontenele\LaravelVaultServer\Exceptions\JwtException;
use JuniorFontenele\LaravelVaultServer\Exceptions\VaultException;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Models\ClientModel;

class JwtService
{
    /**
     * Validate a JWT token.
     *
     * @param string $jwt
     * @param array<int, string> $scopes
     * @throws JwtException
     * @throws VaultException
     */
    public function validate(string $jwt, array $scopes = []): void
    {
        $decodedJwt = $this->decode($jwt);

        $payload = (array) $decodedJwt;

        if (empty($payload['nonce'])) {
            throw new JwtException('Nonce not found in JWT');
        }

        if (Cache::has('vault:nonce:' . $payload['nonce'])) {
            throw new JwtException('Nonce already used');
        }

        if (empty($payload['jti'])) {
            throw new JwtException('JTI not found in JWT');
        }

        if (Cache::has('vault:jti:' . $payload['jti'])) {
            throw new JwtException('Token is blacklisted');
        }

        if ($scopes !== []) {
            $scopes = array_map('strtolower', $scopes);

            $client = ClientModel::query()
                ->active()
                ->where('id', $payload['client_id'])
                ->first();

            if (! $client) {
                throw new VaultException('Client not found');
            }

            $allowedScopes = $client->allowed_scopes ?? [];

            foreach ($scopes as $scope) {
                if (! in_array($scope, $allowedScopes)) {
                    throw new JwtException('Insufficient scope');
                }
            }
        }

        Cache::put('vault:nonce:' . $payload['nonce'], true, $payload['exp'] - time());
        Cache::put('vault:jti:' . $payload['jti'], true, $payload['exp'] - time());
    }

    public function getKidFromJwt(string $jwt): ?string
    {
        $header = json_decode(base64_decode(explode('.', $jwt)[0]), true);

        return $header['kid'] ?? null;
    }

    public function decode(string $jwt): object
    {
        $kid = $this->getKidFromJwt($jwt);

        if ($kid === null || $kid === '' || $kid === '0') {
            throw new JwtException('Kid not found in JWT');
        }

        $keyResponseDTO = VaultKey::findByKid($kid);

        if (empty($keyResponseDTO)) {
            throw new VaultException('Public key not found for kid: ' . $kid);
        }

        $decodedJwt = JWT::decode($jwt, new Key($keyResponseDTO->publicKey, 'RS256'));

        $payload = (array) $decodedJwt;

        if ($payload['client_id'] !== $keyResponseDTO->clientId) {
            throw new JwtException('Invalid client_id');
        }

        return $decodedJwt;
    }
}
