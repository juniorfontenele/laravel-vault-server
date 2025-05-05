<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Hash;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Hash\HashResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\UnitOfWorkInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Contracts\HashRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Hash;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\HashId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\ValueObjects\UserId;

class StoreHashForUserId
{
    public function __construct(
        protected readonly HashRepositoryInterface $hashRepository,
        protected readonly UnitOfWorkInterface $unitOfWork,
    ) {
        //
    }

    public function execute(string $userId, string $hash): HashResponseDTO
    {
        return $this->unitOfWork->execute(function () use ($userId, $hash) {
            $hashes = $this->hashRepository->findAllHashes();

            foreach ($hashes as $hashEntity) {
                $hashEntity->revoke();

                $this->hashRepository->save($hashEntity);
            }

            $newHash = new Hash(
                hashId: new HashId(),
                userId: new UserId($userId),
                hash: $hash,
                version: $this->hashRepository->maxVersion($userId) + 1,
                isRevoked: false,
                revokedAt: null,
            );

            $this->hashRepository->save($newHash);

            return new HashResponseDTO(
                hashId: $newHash->hashId(),
                userId: $newHash->userId(),
                hash: $newHash->hash(),
                version: $newHash->version(),
                isRevoked: $newHash->isRevoked(),
                revokedAt: $newHash->revokedAt(),
            );
        });
    }
}
