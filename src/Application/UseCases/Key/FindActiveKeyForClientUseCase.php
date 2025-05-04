<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Key;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;

class FindActiveKeyForClientUseCase
{
    public function __construct(
        private readonly KeyRepositoryInterface $keyRepository,
    ) {
        //
    }

    public function execute(string $clientId): ?Key
    {
        return $this->keyRepository->findActiveKeyByClientId($clientId);
    }
}
