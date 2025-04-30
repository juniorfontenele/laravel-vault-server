<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\ClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;

class DeleteInactiveClients
{
    public function __construct(protected readonly ClientRepositoryInterface $clientRepository)
    {
    }

    /**
     * @return ClientResponseDTO[]
     */
    public function execute(): array
    {
        $deletedClients = $this->clientRepository->findAllInactiveClients();

        $this->clientRepository->deleteAllInactiveClients();

        return array_map(fn (Client $client) => new ClientResponseDTO(
            clientId: $client->clientId(),
            name: $client->name(),
            allowedScopes: $client->scopes(),
            description: $client->description(),
        ), $deletedClients);
    }
}
