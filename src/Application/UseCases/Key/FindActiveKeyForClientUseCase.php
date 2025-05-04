<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Key;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\KeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;

class FindActiveKeyForClientUseCase
{
    public function __construct(
        private readonly KeyRepositoryInterface $keyRepository,
    ) {
        //
    }

    public function execute(string $clientId): ?KeyResponseDTO
    {
        $key = $this->keyRepository->findActiveKeyByClientId($clientId);

        if (! $key instanceof Key) {
            return null;
        }

        return new KeyResponseDTO(
            keyId: $key->keyId(),
            clientId: $key->clientId(),
            publicKey: $key->publicKey(),
            version: $key->version(),
            validFrom: $key->validFrom(),
            validUntil: $key->validUntil(),
            isRevoked: $key->isRevoked(),
            revokedAt: $key->revokedAt(),
        );
    }
}
