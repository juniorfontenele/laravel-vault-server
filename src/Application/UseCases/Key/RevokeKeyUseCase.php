<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Key;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Exceptions\PublicKeyException;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;

class RevokeKeyUseCase
{
    public function __construct(
        protected readonly KeyRepositoryInterface $keyRepository,
    ) {
        //
    }

    public function execute(string $keyId): void
    {
        $key = $this->keyRepository->findKeyByKeyId($keyId);

        if (! $key instanceof Key) {
            throw PublicKeyException::notFound($keyId);
        }

        $key->revoke();

        $this->keyRepository->save($key);
    }
}
