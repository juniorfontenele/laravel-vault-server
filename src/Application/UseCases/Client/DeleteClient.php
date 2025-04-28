<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Domains\Client\Exceptions\ClientException;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Repositories\ClientRepositoryInterface;

class DeleteClient
{
    public function __construct(protected readonly ClientRepositoryInterface $clientRepository)
    {
    }

    public function handle(string $clientId): void
    {
        $client = $this->clientRepository->findById($clientId);

        if (is_null($client)) {
            throw ClientException::notFound($clientId);
        }

        $this->clientRepository->delete($client);
    }
}
