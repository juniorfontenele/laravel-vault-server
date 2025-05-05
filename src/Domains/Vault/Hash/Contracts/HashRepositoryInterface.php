<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Contracts;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Hash;

interface HashRepositoryInterface
{
    public function findActiveHashByUserId(string $userId): ?Hash;

    public function save(Hash $hash): void;

    public function delete(string $hashId): void;

    public function deleteAllHashesForUserId(string $userId): void;

    /**
     * @return Hash[]
     */
    public function findAllHashesForUserId(string $userId): array;

    /**
     * @return Hash[]
     */
    public function findAllRevokedHashesForUserId(string $userId): array;

    /**
     * @return Hash[]
     */
    public function findAllRevokedHashes(): array;

    /**
     * @return Hash[]
     */
    public function findAllHashes(): array;

    public function maxVersion(string $userId): int;
}
