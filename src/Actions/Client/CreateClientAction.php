<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Actions\Client;

use JuniorFontenele\LaravelVaultServer\Data\Client\ClientCreatedData;
use JuniorFontenele\LaravelVaultServer\Data\Client\CreateClientData;
use JuniorFontenele\LaravelVaultServer\Events\Client\VaultClientCreated;
use JuniorFontenele\LaravelVaultServer\Models\ClientModel;

class CreateClientAction
{
    public function execute(CreateClientData $data): ClientCreatedData
    {
        $client = ClientModel::create($data->toArray());

        $clientCreatedData = new ClientCreatedData(
            id: $client->id,
            name: $client->name,
            allowed_scopes: $client->allowed_scopes,
            provision_token: $data->provision_token,
            description: $client->description,
            created_at: $client->created_at,
        );

        event(new VaultClientCreated($clientCreatedData));

        return $clientCreatedData;
    }
}
