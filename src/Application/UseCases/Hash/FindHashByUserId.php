<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Hash;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Hash\HashResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Contracts\HashRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Hash;

class FindHashByUserId
{
    public function __construct(
        public readonly HashRepositoryInterface $hashRepository,
    ) {
        //
    }

    public function execute(string $userId): ?HashResponseDTO
    {
        $hash = $this->hashRepository->findActiveHashByUserId($userId);

        if (! $hash instanceof Hash) {
            return null;
        }

        return new HashResponseDTO(
            hashId: $hash->hashId(),
            userId: $hash->userId(),
            hash: $hash->hash(),
            version: $hash->version(),
            isRevoked: $hash->isRevoked(),
            revokedAt: $hash->revokedAt(),
        );
    }
}
