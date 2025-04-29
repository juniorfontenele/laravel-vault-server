<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Entities\Client;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Repositories\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Client\ValueObjects\AllowedScopes;
use JuniorFontenele\LaravelVaultServer\Domains\Client\ValueObjects\ProvisionToken;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\ValueObjects\Id;

class CreateClient
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
        //
    }

    public function execute(CreateClientDTO $clientDTO): CreateClientResponseDTO
    {
        $client = new Client(
            id: new Id(),
            name: $clientDTO->name,
            allowedScopes: AllowedScopes::fromStringArray($clientDTO->allowedScopes),
            description: $clientDTO->description,
            provisionToken: new ProvisionToken(),
        );

        $this->clientRepository->save($client);

        return new CreateClientResponseDTO(
            id: $client->id(),
            name: $client->name,
            allowedScopes: $client->scopes(),
            provisionToken: $client->provisionToken(),
            description: $clientDTO->description,
        );
    }
}
