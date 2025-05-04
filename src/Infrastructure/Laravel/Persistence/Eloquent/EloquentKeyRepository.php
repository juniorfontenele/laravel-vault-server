<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Eloquent;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Mappers\KeyMapper;
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
        $model = KeyMapper::toEloquent($key);

        $model->save();
    }

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
}
