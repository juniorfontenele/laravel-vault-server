<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects\AllowedScopes;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects\ProvisionToken;

class CreateClientUseCase
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
        //
    }

    public function execute(CreateClientDTO $clientDTO): CreateClientResponseDTO
    {
        $client = new Client(
            clientId: new ClientId(),
            name: $clientDTO->name,
            allowedScopes: AllowedScopes::fromStringArray($clientDTO->allowedScopes),
            description: $clientDTO->description,
            provisionToken: new ProvisionToken(),
        );

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
