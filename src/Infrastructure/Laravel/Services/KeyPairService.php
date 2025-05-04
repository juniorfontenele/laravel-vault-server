<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Models\ClientModel;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Models\KeyModel;
use phpseclib3\Crypt\RSA;

class KeyPairService
{
    /**
     * Rotate a key.
     *
     * @param KeyModel $key
     * @return array{0: KeyModel, 1: string}
     */
    public function rotate(KeyModel $key, int $expiresIn = 365): array
    {
        return DB::transaction(function () use ($key, $expiresIn) {
            [$publicKey, $privateKey] = $this->generateKeyPair();

            $newKey = $key->client->keys()->create([
                'public_key' => $publicKey,
                'valid_from' => now(),
                'valid_until' => now()->addDays($expiresIn),
                'is_revoked' => false,
            ]);

            $key->revoke();

            Event::dispatch('vault.key.rotated', [$key, $newKey]);

            return [$newKey, $privateKey];
        });
    }

    /**
     * Create a new key for a client.
     *
     * @param ClientModel $client
     * @param int $bits
     * @param int $expiresIn
     * @return array{0: KeyModel, 1: string}
     */
    public function createKeyForClient(ClientModel $client, int $bits = 2048, int $expiresIn = 365): array
    {
        [$publicKey, $privateKey] = $this->generateKeyPair($bits);

        return DB::transaction(function () use ($client, $publicKey, $privateKey, $expiresIn) {
            $client->keys()->valid()->each(fn (KeyModel $key) => $this->revokeKey($key));

            $key = $client->keys()->create([
                'public_key' => $publicKey,
                'valid_from' => now(),
                'valid_until' => now()->addDays($expiresIn),
                'is_revoked' => false,
            ]);

            Event::dispatch('vault.key.created', [$key]);

            return [$key, $privateKey];
        });
    }

    /**
     * Generate a new key pair.
     *
     * @return array{0: string, 1: string}
     */
    public function generateKeyPair(int $bits = 2048): array
    {
        $privateKey = RSA::createKey($bits);

        return [
            $privateKey->getPublicKey()->toString('PKCS8'),
            $privateKey->toString('PKCS8'),
        ];
    }

    /**
     * Find a key by its KID.
     *
     * @param string $kid
     * @return KeyModel|null
     */
    public function findByKid(string $kid): ?KeyModel
    {
        Event::dispatch('vault.key.findByKid', [$kid]);

        return KeyModel::query()->valid()->where('id', $kid)->first();
    }

    public function findByClientId(string $clientId): ?KeyModel
    {
        Event::dispatch('vault.key.findByClientId', [$clientId]);

        $client = ClientModel::query()
            ->active()
            ->where('id', $clientId)
            ->first();

        if (empty($client)) {
            return null;
        }

        return $client->key;
    }

    /**
     * Revoke a key.
     * @param KeyModel $key
     * @return bool
     */
    public function revokeKey(KeyModel $key): bool
    {
        $key->revoke();

        Event::dispatch('vault.key.revoked', [$key->refresh()]);

        return true;
    }

    /**
     * Delete a key.
     * @param KeyModel $key
     * @return bool
     */
    public function deleteKey(KeyModel $key): bool
    {
        $key->delete();

        Event::dispatch('vault.key.deleted', [$key]);

        return true;
    }

    /**
     * Cleanup expired keys.
     * @return int Number of expired keys deleted
     */
    public function cleanupExpiredKeys(): int
    {
        $expired = KeyModel::query()->expired()->get();

        if ($expired->isEmpty()) {
            return 0;
        }

        foreach ($expired as $key) {
            $key->delete();
        }

        Event::dispatch('vault.key.cleanupExpired', [$expired]);

        return $expired->count();
    }

    /**
     * Cleanup revoked keys.
     * @return int Number of revoked keys deleted
     */
    public function cleanupRevokedKeys(): int
    {
        $revoked = KeyModel::query()->revoked()->get();

        if ($revoked->isEmpty()) {
            return 0;
        }

        foreach ($revoked as $key) {
            $key->delete();
        }

        Event::dispatch('vault.key.cleanupRevoked', [$revoked]);

        return $revoked->count();
    }
}
