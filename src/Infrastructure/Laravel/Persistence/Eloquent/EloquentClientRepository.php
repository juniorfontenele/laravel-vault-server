<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Eloquent;

use Illuminate\Support\Facades\Hash;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects\AllowedScopes;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects\ProvisionToken;
use JuniorFontenele\LaravelVaultServer\Models\Client as Client;

class EloquentClientRepository implements ClientRepositoryInterface
{
    public function findClientByClientId(string $clientId): ?Client
    {
        $model = Client::query()->find($clientId);

        if (! $model) {
            return null;
        }

        return new Client(
            clientId: new ClientId($model->id),
            name: $model->name,
            allowedScopes: AllowedScopes::fromStringArray($model->allowed_scopes),
            isActive: $model->is_active,
            description: $model->description,
            provisionToken: $model->provision_token ? new ProvisionToken($model->provision_token) : null,
            provisionedAt: $model->provisioned_at,
        );
    }

    public function save(Client $clientEntity): void
    {
        $model = Client::query()->find($clientEntity->clientId()) ?? new Client();

        $model->id = $clientEntity->clientId();
        $model->name = $clientEntity->name();
        $model->allowed_scopes = $clientEntity->scopes();
        $model->description = $clientEntity->description();
        $model->provision_token = $clientEntity->isNotProvisioned() ? Hash::make($clientEntity->provisionToken()) : null;
        $model->provisioned_at = $clientEntity->isNotProvisioned() ? null : $clientEntity->provisionedAt();
        $model->is_active = $clientEntity->isActive();
        $model->save();
    }

    public function delete(Client $clientEntity): void
    {
        Client::query()->where('id', $clientEntity->clientId())->delete();
    }

    /**
     * @return Client[]
     */
    public function findAllInactiveClients(): array
    {
        return Client::query()
            ->inactive()
            ->get()
            ->map(fn (Client $model) => new Client(
                clientId: new ClientId($model->id),
                name: $model->name,
                allowedScopes: AllowedScopes::fromStringArray($model->allowed_scopes),
                isActive: $model->is_active,
                description: $model->description,
                provisionToken: $model->provision_token ? new ProvisionToken($model->provision_token) : null,
                provisionedAt: $model->provisioned_at,
            ))->toArray();
    }

    /**
     * @return Client[]
     */
    public function findAllActiveClients(): array
    {
        return Client::query()
            ->active()
            ->get()
            ->map(fn (Client $model) => new Client(
                clientId: new ClientId($model->id),
                name: $model->name,
                allowedScopes: AllowedScopes::fromStringArray($model->allowed_scopes),
                isActive: $model->is_active,
                description: $model->description,
                provisionToken: $model->provision_token ? new ProvisionToken($model->provision_token) : null,
                provisionedAt: $model->provisioned_at,
            ))->toArray();
    }

    /**
     * @return Client[]
     */
    public function findAllClients(): array
    {
        return Client::query()
            ->get()
            ->map(fn (Client $model) => new Client(
                clientId: new ClientId($model->id),
                name: $model->name,
                allowedScopes: AllowedScopes::fromStringArray($model->allowed_scopes),
                isActive: $model->is_active,
                description: $model->description,
                provisionToken: $model->provision_token ? new ProvisionToken($model->provision_token) : null,
                provisionedAt: $model->provisioned_at,
            ))->toArray();
    }

    public function deleteAllInactiveClients(): void
    {
        Client::query()
            ->inactive()
            ->delete();
    }
}
