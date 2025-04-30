<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;

interface KeyRepositoryInterface
{
    public function findKeyByKeyId(string $keyId): ?Key;

    public function save(Key $key): void;

    public function delete(string $keyId): void;

    /** @return Key[] */
    public function findAllNonRevokedKeys(): array;
}
