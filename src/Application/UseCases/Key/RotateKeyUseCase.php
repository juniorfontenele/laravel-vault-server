<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Key;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\UnitOfWorkInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Exceptions\PublicKeyException;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\KeyId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\PublicKey;
use phpseclib3\Crypt\RSA;

class RotateKeyUseCase
{
    public function __construct(
        private readonly KeyRepositoryInterface $keyRepository,
        private readonly UnitOfWorkInterface $unitOfWork,
    ) {
    }

    public function execute(string $keyId, int $days = 365): CreateKeyResponseDTO
    {
        $key = $this->keyRepository->findKeyByKeyId($keyId);

        if (!$key instanceof \JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key) {
            throw PublicKeyException::notFound($keyId);
        }

        return $this->unitOfWork->execute(function () use ($key, $days) {
            $nonRevokedKeys = $this->keyRepository->findAllNonRevokedKeysByClientId($key->clientId());

            if (count($nonRevokedKeys) > 0) {
                foreach ($nonRevokedKeys as $nonRevokedKey) {
                    $nonRevokedKey->revoke();
                    $this->keyRepository->save($nonRevokedKey);
                }
            }

            $privateKey = RSA::createKey(2048);

            $newKey = new Key(
                keyId: new KeyId(),
                clientId: new ClientId($key->clientId()),
                publicKey: new PublicKey($privateKey->getPublicKey()->toString('PKCS8')),
                version: $this->keyRepository->maxVersion($key->clientId()) + 1,
                validFrom: new \DateTimeImmutable(),
                validUntil: (new \DateTimeImmutable())->modify("+{$days} days"),
            );

            $this->keyRepository->save($newKey);

            return new CreateKeyResponseDTO(
                keyId: $newKey->keyId(),
                clientId: $newKey->clientId(),
                publicKey: $newKey->publicKey(),
                privateKey: $privateKey->toString('PKCS8'),
                version: $newKey->version(),
                validFrom: $newKey->validFrom(),
                validUntil: $newKey->validUntil(),
            );
        });
    }
}
