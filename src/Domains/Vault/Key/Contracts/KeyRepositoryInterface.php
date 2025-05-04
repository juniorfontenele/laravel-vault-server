<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;

interface KeyRepositoryInterface
{
    public function findKeyByKeyId(string $keyId): ?Key;

    public function findActiveKeyByClientId(string $clientId): ?Key;

    public function save(Key $key): void;

    public function delete(string $keyId): void;

    /** @return Key[] */
    public function findAllNonRevokedKeys(): array;

    /** @return Key[] */
    public function findAllKeysForClientId(string $clientId): array;

    /** @return Key[] */
    public function findAllNonRevokedKeysByClientId(string $clientId): array;

    public function maxVersion(string $clientId): int;

    /** @return Key[] */
    public function findAllExpiredKeys(): array;

    /** @return Key[] */
    public function findAllRevokedKeys(): array;
}
