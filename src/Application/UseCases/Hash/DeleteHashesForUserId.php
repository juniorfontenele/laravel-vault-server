<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Hash;

use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\UnitOfWorkInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Contracts\HashRepositoryInterface;

class DeleteHashesForUserId
{
    public function __construct(
        protected readonly HashRepositoryInterface $hashRepository,
        protected readonly UnitOfWorkInterface $unitOfWork,
    ) {
        //
    }

    public function execute(string $userId): void
    {
        $this->hashRepository->deleteAllHashesForUserId($userId);
    }
}
