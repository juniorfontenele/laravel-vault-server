<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\ClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Repositories\ClientRepositoryInterface;

class FindAllActiveClients
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
    ) {
    }

    /**
     * @return ClientResponseDTO[]
     */
    public function handle(): array
    {
        return array_map(fn ($client) => new ClientResponseDTO(
            id: $client->id,
            name: $client->name,
            description: $client->description,
            allowedScopes: $client->scopes()
        ), $this->clientRepository->findAllActive());
    }
}
