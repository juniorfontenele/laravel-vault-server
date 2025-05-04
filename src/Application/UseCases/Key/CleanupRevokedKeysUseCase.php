<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Key;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;

class CleanupRevokedKeysUseCase
{
    public function __construct(
        protected readonly KeyRepositoryInterface $keyRepository,
    ) {
        //
    }

    public function execute(): int
    {
        $revokedKeys = $this->keyRepository->findAllRevokedKeys();

        foreach ($revokedKeys as $key) {
            $this->keyRepository->delete($key->keyId());
        }

        return count($revokedKeys);
    }
}
