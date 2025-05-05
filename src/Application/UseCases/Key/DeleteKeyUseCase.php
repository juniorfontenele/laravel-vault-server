<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Key;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;

class DeleteKeyUseCase
{
    public function __construct(
        protected readonly KeyRepositoryInterface $keyRepository,
    ) {
        //
    }

    public function execute(string $keyId): void
    {
        $this->keyRepository->delete($keyId);
    }
}
