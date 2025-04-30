<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\ClientException;

class DeleteClient
{
    public function __construct(protected readonly ClientRepositoryInterface $clientRepository)
    {
    }

    public function execute(string $clientId): void
    {
        $client = $this->clientRepository->findClientByClientId($clientId);

        if (is_null($client)) {
            throw ClientException::notFound($clientId);
        }

        $this->clientRepository->delete($client);
    }
}
