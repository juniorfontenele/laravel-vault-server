<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Exceptions\ClientException;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Repositories\ClientRepositoryInterface;

class ReprovisionClient
{
    public function __construct(protected readonly ClientRepositoryInterface $clientRepository)
    {
    }

    public function execute(string $clientId): CreateClientResponseDTO
    {
        $client = $this->clientRepository->findById($clientId);

        if (is_null($client)) {
            throw ClientException::notFound($clientId);
        }

        $client->reprovision();

        $this->clientRepository->save($client);

        return new CreateClientResponseDTO(
            id: $client->id(),
            name: $client->name(),
            allowedScopes: $client->scopes(),
            provisionToken: $client->provisionToken(),
            description: $client->description(),
        );
    }
}
