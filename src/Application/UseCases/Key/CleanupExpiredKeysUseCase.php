<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Key;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;

class CleanupExpiredKeysUseCase
{
    public function __construct(
        protected readonly KeyRepositoryInterface $keyRepository,
    ) {
        //
    }

    public function execute(): int
    {
        $expiredKeys = $this->keyRepository->findAllExpiredKeys();

        foreach ($expiredKeys as $key) {
            $this->keyRepository->delete($key->keyId());
        }

        return count($expiredKeys);
    }
}
