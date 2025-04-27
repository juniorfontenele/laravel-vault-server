<?php

declare(strict_types = 1);

namespace App\Application\UseCases\Client;

use App\Application\DTOs\Client\CreateClientDTO;
use App\Application\DTOs\Client\CreateClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Client\Entities\Client;
use JuniorFontenele\LaravelVaultServer\Client\Repositories\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Client\ValueObjects\AllowedScopes;
use JuniorFontenele\LaravelVaultServer\Client\ValueObjects\ProvisionToken;
use Ramsey\Uuid\Uuid;

class CreateClient
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
        //
    }

    public function handle(CreateClientDTO $clientDTO): CreateClientResponseDTO
    {
        $client = new Client(
            id: Uuid::uuid7()->toString(),
            name: $clientDTO->name,
            allowedScopes: AllowedScopes::fromStringArray($clientDTO->allowedScopes),
            description: $clientDTO->description,
            provisionToken: new ProvisionToken(),
        );

        $this->clientRepository->save($client);

        return new CreateClientResponseDTO(
            id: $client->id,
            name: $client->name,
            allowedScopes: $client->scopes(),
            provisionToken: $client->provisionToken(),
            description: $clientDTO->description,
        );
    }
}
