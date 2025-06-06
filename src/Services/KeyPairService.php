<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use JuniorFontenele\LaravelVaultServer\Artifacts\NewKey;
use JuniorFontenele\LaravelVaultServer\Events\Key\ExpiredKeysCleanedUp;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyCreated;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyRetrieved;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyRevoked;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyRotated;
use JuniorFontenele\LaravelVaultServer\Events\Key\RevokedKeysCleanedUp;
use JuniorFontenele\LaravelVaultServer\Exceptions\Key\KeyNotFoundException;
use JuniorFontenele\LaravelVaultServer\Filters\Key\ByClientIdFilter;
use JuniorFontenele\LaravelVaultServer\Filters\Key\ByKeyIdFilter;
use JuniorFontenele\LaravelVaultServer\Filters\Key\ExpiredFilter;
use JuniorFontenele\LaravelVaultServer\Filters\Key\NonRevokedFilter;
use JuniorFontenele\LaravelVaultServer\Filters\Key\RevokedFilter;
use JuniorFontenele\LaravelVaultServer\Models\Key;
use JuniorFontenele\LaravelVaultServer\Queries\KeyQueryBuilder;
use phpseclib3\Crypt\RSA;

class KeyPairService
{
    /**
     * Rotate client keys.
     *
     * @param string $keyId
     * @param int $keySize
     * @param int $expiresIn
     * @return NewKey
     * @throws KeyNotFoundException
     */
    public function rotate(string $keyId, int $keySize = 2048, int $expiresIn = 365): NewKey
    {
        $key = $this->findByKeyId($keyId);

        return $this->create(
            clientId: $key->client_id,
            keySize: $keySize,
            expiresIn: $expiresIn
        );
    }

    /**
     * Create a new key for a client.
     *
     * @param string $clientId
     * @param int $keySize
     * @param int $expiresIn
     * @return NewKey
     */
    public function create(string $clientId, int $keySize = 2048, int $expiresIn = 365): NewKey
    {
        /** @var NewKey $newKey */
        $newKey = DB::transaction(function () use ($clientId, $keySize, $expiresIn): NewKey {
            $clientKeys = $this->findKeysByClientId($clientId);

            $clientKeys->each(function (Key $clientKey) {
                $this->revoke($clientKey->id);
            });

            [$privateKey, $publicKey] = $this->generateKeyPair($keySize);

            $newKey = Key::forceCreate([
                'client_id' => $clientId,
                'algorithm' => 'RS256',
                'public_key' => $publicKey,
                'version' => $this->getMaxVersion($clientId) + 1,
                'valid_from' => now(),
                'valid_until' => now()->addDays($expiresIn),
                'is_revoked' => false,
            ]);

            return new NewKey(
                $newKey,
                $privateKey,
            );
        });

        if ($newKey->key->version === 1) {
            event(new KeyCreated($newKey->key));
        } else {
            event(new KeyRotated($newKey->key));
        }

        return $newKey;
    }

    /**
     * Find a key by its Key ID.
     *
     * @param string $keyId
     * @return Key
     * @throws KeyNotFoundException
     */
    public function get(string $keyId): Key
    {
        $key = $this->findByKeyId($keyId);

        event(new KeyRetrieved($key));

        return $key;
    }

    /**
     * Revoke a key by its Key ID.
     *
     * @param string $keyId
     * @throws KeyNotFoundException
     */
    public function revoke(string $keyId): void
    {
        $key = $this->findByKeyId($keyId, false);

        $key->revoked_at = now();
        $key->is_revoked = true;
        $key->save();

        event(new KeyRevoked($key));
    }

    /**
     * Cleanup expired keys.
     * @return Collection<Key> Collection of expired keys
     */
    public function cleanupExpiredKeys(): Collection
    {
        $expiredKeys = (new KeyQueryBuilder())
            ->addFilter(new ExpiredFilter())
            ->setSelectColumns(['id', 'client_id'])
            ->build()
            ->get();

        event(new ExpiredKeysCleanedUp($expiredKeys));

        return $expiredKeys;
    }

    /**
     * Cleanup revoked keys.
     * @return Collection<Key> Collection of revoked keys
     */
    public function cleanupRevokedKeys(): Collection
    {
        $revokedKeys = (new KeyQueryBuilder())
            ->addFilter(new RevokedFilter())
            ->setSelectColumns(['id', 'client_id'])
            ->build()
            ->get();

        event(new RevokedKeysCleanedUp($revokedKeys));

        return $revokedKeys;
    }

    private function getMaxVersion(string $clientId): int
    {
        return Key::where('client_id', $clientId)
            ->max('version') ?? 0;
    }

    /**
     * Generate a new RSA key pair.
     *
     * @param int $keySize
     * @return array{0: string, 1: string}
     */
    private function generateKeyPair(int $keySize = 2048): array
    {
        $privateKey = RSA::createKey($keySize);
        $publicKey = $privateKey->getPublicKey()->toString('PKCS8');

        return [
            $privateKey->toString('PKCS8'),
            $publicKey,
        ];
    }

    /**
     * Get all keys for a client.
     *
     * @param string $clientId
     * @return Collection<Key>
     */
    private function findKeysByClientId(string $clientId): Collection
    {
        return (new KeyQueryBuilder())
            ->addFilter(new ByClientIdFilter($clientId))
            ->build()
            ->get();
    }

    /**
     * Find a key by its Key ID.
     *
     * @param string $keyId
     * @return Key
     * @throws KeyNotFoundException
     */
    private function findByKeyId(string $keyId, bool $onlyNonRevoked = true): Key
    {
        $query = (new KeyQueryBuilder())
            ->addFilter(new ByKeyIdFilter($keyId));

        if ($onlyNonRevoked) {
            $query->addFilter(new NonRevokedFilter());
        }

        $key = $query->build()
            ->first();

        if (! $key) {
            throw new KeyNotFoundException($keyId);
        }

        return $key;
    }
}
