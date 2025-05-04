<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Key;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\KeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;

class FindAllNonRevokedKeysForClientUseCase
{
    public function __construct(
        protected readonly KeyRepositoryInterface $keyRepository,
    ) {
        //
    }

    /**
     * @return KeyResponseDTO[]
     */
    public function execute(string $clientId): array
    {
        $keys = $this->keyRepository->findAllNonRevokedKeysByClientId($clientId);

        return array_map(function (Key $keyResponseDTO): KeyResponseDTO {
            return new KeyResponseDTO(
                keyId: $keyResponseDTO->keyId(),
                clientId: $keyResponseDTO->clientId(),
                publicKey: $keyResponseDTO->publicKey(),
                version: $keyResponseDTO->version(),
                validFrom: $keyResponseDTO->validFrom(),
                validUntil: $keyResponseDTO->validUntil(),
                isRevoked: $keyResponseDTO->isRevoked(),
                revokedAt: $keyResponseDTO->revokedAt(),
            );
        }, $keys);
    }
}
