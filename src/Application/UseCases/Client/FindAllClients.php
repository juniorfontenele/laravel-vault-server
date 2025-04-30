<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\ClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;

class FindAllClients
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
    ) {
    }

    /**
     * @return ClientResponseDTO[]
     */
    public function execute(): array
    {
        return array_map(fn (Client $client) => new ClientResponseDTO(
            clientId: $client->clientId(),
            name: $client->name(),
            description: $client->description(),
            allowedScopes: $client->scopes()
        ), $this->clientRepository->findAllClients());
    }
}
