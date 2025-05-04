<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Key;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\KeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;

class FindKeyByKeyIdUseCase
{
    public function __construct(
        protected readonly KeyRepositoryInterface $keyRepository,
    ) {
        //
    }

    public function execute(string $keyId): ?KeyResponseDTO
    {
        $key = $this->keyRepository->findKeyByKeyId($keyId);

        if (! $key) {
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
