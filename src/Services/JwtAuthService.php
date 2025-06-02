<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Services;

use JuniorFontenele\LaravelSecureJwt\Exceptions\JwtInvalidKidException;
use JuniorFontenele\LaravelSecureJwt\Exceptions\JwtValidationException;
use JuniorFontenele\LaravelSecureJwt\Exceptions\NonceUsedException;
use JuniorFontenele\LaravelSecureJwt\Exceptions\TokenBlacklistedException;
use JuniorFontenele\LaravelSecureJwt\Facades\SecureJwt as SecureJwtFacade;
use JuniorFontenele\LaravelSecureJwt\JwtKey;
use JuniorFontenele\LaravelSecureJwt\SecureJwt;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotAuthenticatedException;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotAuthorizedException;
use JuniorFontenele\LaravelVaultServer\Exceptions\Jwt\InvalidJwtHeader;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Models\Key;

class JwtAuthService
{
    private ?SecureJwt $jwt = null;

    private ?Key $key = null;

    private bool $isAuthenticated = false;

    /**
     * Attempt to authenticate a client using the provided JWT token.
     *
     * @param string $token
     * @return Key
     * @throws InvalidJwtHeader
     * @throws JwtInvalidKidException
     * @throws TokenBlacklistedException
     * @throws NonceUsedException
     * @throws JwtValidationException
     */
    public function attempt(string $token): Key
    {
        $header = $this->decodeHeaderFromBase64Token($token);

        $key = VaultKey::get($header['kid']);

        $this->jwt = SecureJwtFacade::decode($token, new JwtKey(
            $key->id,
            $key->public_key,
            $key->algorithm
        ));

        $this->key = $key;
        $this->isAuthenticated = true;

        return $key;
    }

    public function logout(): void
    {
        if (! $this->isAuthenticated) {
            return;
        }

        SecureJwtFacade::blacklist($this->jwt->jti());

        $this->key = null;
        $this->jwt = null;
        $this->isAuthenticated = false;
    }

    public function check(): bool
    {
        return $this->isAuthenticated;
    }

    public function can(string $requestedScope): bool
    {
        if (! $this->isAuthenticated) {
            return false;
        }

        if (is_null($this->key)) {
            return false;
        }

        try {
            $this->authorizeScopes($this->key->client, $requestedScope);

            return true;
        } catch (ClientNotAuthorizedException) {
            return false;
        }
    }

    public function authorize(string $requestedScope): void
    {
        if (! $this->isAuthenticated) {
            throw new ClientNotAuthenticatedException();
        }

        if (is_null($this->key)) {
            throw new ClientNotAuthenticatedException();
        }

        $this->authorizeScopes($this->key->client, $requestedScope);
    }

    public function key(): ?Key
    {
        return $this->key;
    }

    public function client(): ?Client
    {
        return $this->key?->client;
    }

    /**
     * Authorize the requested scope against the client's allowed scopes.
     *
     * @param Client $client
     * @param string $requestedScope
     * @throws ClientNotAuthorizedException
     */
    private function authorizeScopes(Client $client, string $requestedScope): void
    {
        $requestedScope = strtolower($requestedScope);
        $allowedScopes = array_map('strtolower', $client->allowed_scopes);

        if (! in_array($requestedScope, $allowedScopes, true)) {
            throw new ClientNotAuthorizedException($requestedScope);
        }
    }

    /**
     * Decode the JWT header from a base64 token.
     *
     * @param string $token
     * @return array{kid: string, alg: string, typ: string}
     * @throws InvalidJwtHeader
     */
    private function decodeHeaderFromBase64Token(string $token): array
    {
        [$header, $payload, $signature] = explode('.', $token);
        $decodedHeader = json_decode(base64_decode($header), true);

        if ($decodedHeader === false) {
            throw new InvalidJwtHeader();
        }

        if (! isset($decodedHeader['kid'], $decodedHeader['alg'], $decodedHeader['typ'])) {
            throw new InvalidJwtHeader();
        }

        return $decodedHeader;
    }
}
