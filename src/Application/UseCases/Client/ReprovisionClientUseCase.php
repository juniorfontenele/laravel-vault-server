<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Exceptions\ClientException;

class ReprovisionClientUseCase
{
    public function __construct(protected readonly ClientRepositoryInterface $clientRepository)
    {
    }

    public function execute(string $clientId): CreateClientResponseDTO
    {
        $client = $this->clientRepository->findClientByClientId($clientId);

        if (is_null($client)) {
            throw ClientException::notFound($clientId);
        }

        $client->reprovision();

        $this->clientRepository->save($client);

        return new CreateClientResponseDTO(
            clientId: $client->clientId(),
            name: $client->name(),
            allowedScopes: $client->scopes(),
            provisionToken: $client->provisionToken(),
            description: $client->description(),
        );
    }
}
