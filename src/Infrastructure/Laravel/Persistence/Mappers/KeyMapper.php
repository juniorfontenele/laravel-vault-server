<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Mappers;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\KeyId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\PublicKey;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Models\KeyModel;

class KeyMapper
{
    public static function toDomain(KeyModel $keyModel): Key
    {
        return new Key(
            keyId: new KeyId($keyModel->id),
            clientId: new ClientId($keyModel->client_id),
            publicKey: new PublicKey($keyModel->public_key),
            version: $keyModel->version,
            validFrom: $keyModel->valid_from,
            validUntil: $keyModel->valid_until,
            isRevoked: $keyModel->is_revoked,
            revokedAt: $keyModel->revoked_at,
        );
    }

    public static function toEloquent(Key $key, ?KeyModel $keyModel = null): KeyModel
    {
        $keyModel = $keyModel ?? new KeyModel();

        $keyModel->id = $key->keyId();
        $keyModel->client_id = $key->clientId();
        $keyModel->public_key = $key->publicKey();
        $keyModel->version = $key->version();
        $keyModel->valid_from = $key->validFrom();
        $keyModel->valid_until = $key->validUntil();
        $keyModel->is_revoked = $key->isRevoked();
        $keyModel->revoked_at = $key->revokedAt();

        return $keyModel;
    }
}
