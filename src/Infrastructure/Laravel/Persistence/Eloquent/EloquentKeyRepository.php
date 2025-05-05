<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Eloquent;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Mappers\KeyMapper;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Models\ClientModel;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Models\KeyModel;

class EloquentKeyRepository implements KeyRepositoryInterface
{
    public function delete(string $keyId): void
    {
        KeyModel::query()->where('id', $keyId)->delete();
    }

    public function findKeyByKeyId(string $keyId): ?Key
    {
        $key = KeyModel::find($keyId);

        if (! $key) {
            return null;
        }

        return KeyMapper::toDomain($key);
    }

    public function save(Key $key): void
    {
        $keyModel = KeyModel::query()->find($key->keyId());

        $keyModel = KeyMapper::toEloquent($key, $keyModel);

        $keyModel->save();
    }

    /**
     * @return Key[]
     */
    public function findAllNonRevokedKeys(): array
    {
        return KeyModel::query()
            ->where('is_revoked', false)
            ->get()
            ->map(
                fn (KeyModel $key) => KeyMapper::toDomain($key),
            )
            ->toArray();
    }

    public function findActiveKeyByClientId(string $clientId): ?Key
    {
        $keyModel = ClientModel::query()
            ->where('id', $clientId)
            ->with('key')
            ->first()
            ?->key;

        if (! $keyModel) {
            return null;
        }

        return KeyMapper::toDomain($keyModel);
    }

    /**
     * @return Key[]
     */
    public function findAllNonRevokedKeysByClientId(string $clientId): array
    {
        return KeyModel::query()
            ->where('client_id', $clientId)
            ->where('is_revoked', false)
            ->get()
            ->map(
                fn (KeyModel $key) => KeyMapper::toDomain($key),
            )
            ->toArray();
    }

    public function maxVersion(string $clientId): int
    {
        return KeyModel::query()
            ->where('client_id', $clientId)
            ->max('version') ?? 0;
    }

    /**
     * @return Key[]
     */
    public function findAllKeysForClientId(string $clientId): array
    {
        return KeyModel::query()
            ->where('client_id', $clientId)
            ->get()
            ->map(
                fn (KeyModel $key) => KeyMapper::toDomain($key),
            )
            ->toArray();
    }

    /**
     * @return Key[]
     */
    public function findAllExpiredKeys(): array
    {
        return KeyModel::query()
            ->expired()
            ->get()
            ->map(
                fn (KeyModel $key) => KeyMapper::toDomain($key),
            )
            ->toArray();
    }

    /**
     * @return Key[]
     */
    public function findAllRevokedKeys(): array
    {
        return KeyModel::query()
            ->revoked()
            ->get()
            ->map(
                fn (KeyModel $key) => KeyMapper::toDomain($key),
            )
            ->toArray();
    }
}
