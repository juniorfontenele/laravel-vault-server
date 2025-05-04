<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Key;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\KeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;

class FindAllKeysForClientUseCase
{
    public function __construct(
        protected KeyRepositoryInterface $keyRepository,
    ) {
        //
    }

    /**
     * @return KeyResponseDTO[]
     */
    public function execute(string $clientId): array
    {
        $keys = $this->keyRepository->findAllKeysForClientId($clientId);

        return array_map(function (Key $key) {
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
        }, $keys);
    }
}
