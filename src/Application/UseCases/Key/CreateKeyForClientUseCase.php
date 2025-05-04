<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Key;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\UnitOfWorkInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\KeyId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\PublicKey;
use phpseclib3\Crypt\RSA;

class CreateKeyForClientUseCase
{
    public function __construct(
        protected readonly KeyRepositoryInterface $keyRepository,
        protected readonly UnitOfWorkInterface $unitOfWork,
        protected readonly FindAllKeysForClientUseCase $findAllKeysForClientUseCase,
        protected readonly RevokeKeyUseCase $revokeKeyUseCase,
    ) {
        //
    }

    public function execute(CreateKeyDTO $createKeyDTO): CreateKeyResponseDTO
    {
        return $this->unitOfWork->execute(function () use ($createKeyDTO) {
            $clientKeys = $this->findAllKeysForClientUseCase->execute($createKeyDTO->clientId);

            foreach ($clientKeys as $activeKey) {
                $this->revokeKeyUseCase->execute($activeKey->keyId);
            }

            $privateKey = RSA::createKey(2048);
            $publicKeyString = $privateKey->getPublicKey()->toString('PKCS8');
            $privateKeyString = $privateKey->toString('PKCS8');

            $key = new Key(
                keyId: new KeyId(),
                clientId: new ClientId($createKeyDTO->clientId),
                publicKey: new PublicKey($publicKeyString),
                version: $this->keyRepository->maxVersion($createKeyDTO->clientId) + 1,
                validFrom: new \DateTimeImmutable(),
                validUntil: (new \DateTimeImmutable())->modify("+{$createKeyDTO->days} days"),
                isRevoked: false,
            );

            $this->keyRepository->save($key);

            return new CreateKeyResponseDTO(
                keyId: $key->keyId(),
                clientId: $key->clientId(),
                publicKey: $key->publicKey(),
                privateKey: $privateKeyString,
                version: $key->version(),
                validFrom: $key->validFrom(),
                validUntil: $key->validUntil(),
            );
        });
    }
}
