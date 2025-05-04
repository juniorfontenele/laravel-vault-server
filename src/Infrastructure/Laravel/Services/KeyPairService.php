<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Services;

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\KeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\CleanupExpiredKeysUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\CleanupRevokedKeysUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\CreateKeyForClientUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\DeleteKeyUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\FindActiveKeyForClientUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\FindAllKeysForClientUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\FindAllNonRevokedKeysForClientUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\FindKeyByKeyIdUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\RevokeKeyUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\RotateKeyUseCase;

class KeyPairService
{
    /**
     * Rotate a key.
     *
     * @param string $keyId
     * @param int $keySize
     * @param int $expiresIn
     * @return CreateKeyResponseDTO
     * @throws \Exception
     */
    public function rotate(string $keyId, int $keySize = 2048, int $expiresIn = 365): CreateKeyResponseDTO
    {
        $createKeyResponseDTO = app(RotateKeyUseCase::class)->execute($keyId, $keySize, $expiresIn);

        Event::dispatch('vault.key.rotated', [$createKeyResponseDTO]);

        return $createKeyResponseDTO;
    }

    /**
     * Create a new key for a client.
     *
     * @param string $clientId
     * @param int $keySize
     * @param int $expiresIn
     * @return CreateKeyResponseDTO
     * @throws \Exception
     */
    public function createKeyForClient(string $clientId, int $keySize = 2048, int $expiresIn = 365): CreateKeyResponseDTO
    {
        $createKeyResponseDTO = app(CreateKeyForClientUseCase::class)->execute(new CreateKeyDTO(
            clientId: $clientId,
            keySize: $keySize,
            expiresIn: $expiresIn,
        ));

        Event::dispatch('vault.key.created', [$createKeyResponseDTO]);

        return $createKeyResponseDTO;
    }

    /**
     * Find a key by its KID.
     *
     * @param string $kid
     * @return ?KeyResponseDTO
     */
    public function findByKid(string $kid): ?KeyResponseDTO
    {
        Event::dispatch('vault.key.findByKid', [$kid]);

        return app(FindKeyByKeyIdUseCase::class)->execute($kid);
    }

    public function findByClientId(string $clientId): ?KeyResponseDTO
    {
        Event::dispatch('vault.key.findByClientId', [$clientId]);

        return app(FindActiveKeyForClientUseCase::class)->execute($clientId);
    }

    /**
     * Find all keys for a client.
     *
     * @param string $clientId
     * @return KeyResponseDTO[]
     */
    public function findAllKeysByClientId(string $clientId): array
    {
        Event::dispatch('vault.key.findAllKeysByClientId', [$clientId]);

        return app(FindAllKeysForClientUseCase::class)->execute($clientId);
    }

    /**
     * Find all non-revoked keys for a client.
     *
     * @param string $clientId
     * @return KeyResponseDTO[]
     */
    public function findAllNonRevokedKeysByClientId(string $clientId): array
    {
        Event::dispatch('vault.key.findAllNonRevokedKeysByClientId', [$clientId]);

        return app(FindAllNonRevokedKeysForClientUseCase::class)->execute($clientId);
    }

    /**
     * Revoke a key.
     * @param string $keyId
     * @return bool
     */
    public function revokeKey(string $keyId): bool
    {
        try {
            app(RevokeKeyUseCase::class)->execute($keyId);

            Event::dispatch('vault.key.revoked', [$keyId]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete a key.
     * @param string $keyId
     * @return bool
     */
    public function deleteKey(string $keyId): bool
    {
        try {
            app(DeleteKeyUseCase::class)->execute($keyId);

            Event::dispatch('vault.key.deleted', [$keyId]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Cleanup expired keys.
     * @return int Number of expired keys deleted
     */
    public function cleanupExpiredKeys(): int
    {
        $expiredCount = app(CleanupExpiredKeysUseCase::class)->execute();

        Event::dispatch('vault.key.cleanupExpired', [$expiredCount]);

        return $expiredCount;
    }

    /**
     * Cleanup revoked keys.
     * @return int Number of revoked keys deleted
     */
    public function cleanupRevokedKeys(): int
    {
        $revokedCount = app(CleanupRevokedKeysUseCase::class)->execute();

        Event::dispatch('vault.key.cleanupRevoked', [$revokedCount]);

        return $revokedCount;
    }
}
