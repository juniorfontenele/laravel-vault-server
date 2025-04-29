<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\ClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Entities\Client;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Repositories\ClientRepositoryInterface;

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
        $deletedClients = $this->clientRepository->findAllInactive();

        $this->clientRepository->deleteAllInactive();

        return array_map(fn (Client $client) => new ClientResponseDTO(
            id: $client->id(),
            name: $client->name(),
            allowedScopes: $client->scopes(),
            description: $client->description(),
        ), $deletedClients);
    }
}
