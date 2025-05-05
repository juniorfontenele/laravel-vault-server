<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Eloquent;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Contracts\HashRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Hash;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Models\HashModel;

class EloquentHashRepository implements HashRepositoryInterface
{
    public function findActiveHashByUserId(string $userId): ?Hash
    {
        $nonRevokedHash = HashModel::query()
            ->where('user_id', $userId)
            ->nonRevoked()
            ->orderByDesc('version')
            ->first();

        if (! $nonRevokedHash) {
            return null;
        }

        return new Hash(
            hashId: $nonRevokedHash->id,
            userId: $nonRevokedHash->user_id,
            hash: $nonRevokedHash->hash,
            version: $nonRevokedHash->version,
            isRevoked: $nonRevokedHash->is_revoked,
            revokedAt: $nonRevokedHash->revoked_at,
        );
    }

    public function save(Hash $hash): void
    {
        $hashModel = HashModel::find($hash->hashId()) ?? new HashModel();

        $hashModel->id = $hash->hashId();
        $hashModel->user_id = $hash->userId();
        $hashModel->hash = $hash->hash();
        $hashModel->version = $hash->version();
        $hashModel->is_revoked = $hash->isRevoked();
        $hashModel->revoked_at = $hash->revokedAt();

        $hashModel->saveOrFail();
    }

    public function delete(string $hashId): void
    {
        HashModel::query()
            ->where('id', $hashId)
            ->delete();
    }

    public function deleteAllHashesForUserId(string $userId): void
    {
        HashModel::query()
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * @return Hash[]
     */
    public function findAllHashesForUserId(string $userId): array
    {
        return HashModel::query()
            ->where('user_id', $userId)
            ->orderByDesc('version')
            ->get()
            ->map(
                static fn (HashModel $hashModel): Hash => new Hash(
                    hashId: $hashModel->id,
                    userId: $hashModel->user_id,
                    hash: $hashModel->hash,
                    version: $hashModel->version,
                    isRevoked: $hashModel->is_revoked,
                    revokedAt: $hashModel->revoked_at,
                ),
            )->toArray();
    }

    /**
     * @return Hash[]
     */
    public function findAllRevokedHashesForUserId(string $userId): array
    {
        return HashModel::query()
            ->where('user_id', $userId)
            ->revoked()
            ->orderByDesc('version')
            ->get()
            ->map(
                static fn (HashModel $hashModel): Hash => new Hash(
                    hashId: $hashModel->id,
                    userId: $hashModel->user_id,
                    hash: $hashModel->hash,
                    version: $hashModel->version,
                    isRevoked: $hashModel->is_revoked,
                    revokedAt: $hashModel->revoked_at,
                ),
            )->toArray();
    }

    /**
     * @return Hash[]
     */
    public function findAllRevokedHashes(): array
    {
        return HashModel::query()
            ->revoked()
            ->orderByDesc('version')
            ->get()
            ->map(
                static fn (HashModel $hashModel): Hash => new Hash(
                    hashId: $hashModel->id,
                    userId: $hashModel->user_id,
                    hash: $hashModel->hash,
                    version: $hashModel->version,
                    isRevoked: $hashModel->is_revoked,
                    revokedAt: $hashModel->revoked_at,
                ),
            )->toArray();
    }

    /**
     * @return Hash[]
     */
    public function findAllHashes(): array
    {
        return HashModel::query()
            ->orderByDesc('version')
            ->get()
            ->map(
                static fn (HashModel $hashModel): Hash => new Hash(
                    hashId: $hashModel->id,
                    userId: $hashModel->user_id,
                    hash: $hashModel->hash,
                    version: $hashModel->version,
                    isRevoked: $hashModel->is_revoked,
                    revokedAt: $hashModel->revoked_at,
                ),
            )->toArray();
    }

    public function maxVersion(string $userId): int
    {
        return HashModel::query()
            ->where('user_id', $userId)
            ->max('version') ?? 0;
    }
}
