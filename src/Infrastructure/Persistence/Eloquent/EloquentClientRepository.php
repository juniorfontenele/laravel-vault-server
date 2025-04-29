<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Facades\Hash;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Entities\Client;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Repositories\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Client\ValueObjects\AllowedScopes;
use JuniorFontenele\LaravelVaultServer\Domains\Client\ValueObjects\ClientId;
use JuniorFontenele\LaravelVaultServer\Models\Client as ClientModel;

class EloquentClientRepository implements ClientRepositoryInterface
{
    public function findClientByClientId(string $clientId): ?Client
    {
        $model = ClientModel::query()->find($clientId);

        if (! $model) {
            return null;
        }

        return new Client(
            clientId: new ClientId($model->id),
            name: $model->name,
            allowedScopes: AllowedScopes::fromStringArray($model->allowed_scopes),
            isActive: $model->is_active,
            description: $model->description,
            provisionedAt: $model->provisioned_at,
        );
    }

    public function save(Client $clientEntity): void
    {
        $model = ClientModel::query()->find($clientEntity->clientId()) ?? new ClientModel();

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
        ClientModel::query()->where('id', $clientEntity->clientId())->delete();
    }

    /**
     * @return Client[]
     */
    public function findAllInactiveClients(): array
    {
        return ClientModel::query()
            ->inactive()
            ->get()
            ->map(fn (ClientModel $model) => new Client(
                clientId: new ClientId($model->id),
                name: $model->name,
                allowedScopes: AllowedScopes::fromStringArray($model->allowed_scopes),
                isActive: $model->is_active,
                description: $model->description,
                provisionedAt: $model->provisioned_at,
            ))->toArray();
    }

    /**
     * @return Client[]
     */
    public function findAllActiveClients(): array
    {
        return ClientModel::query()
            ->active()
            ->get()
            ->map(fn (ClientModel $model) => new Client(
                clientId: new ClientId($model->id),
                name: $model->name,
                allowedScopes: AllowedScopes::fromStringArray($model->allowed_scopes),
                isActive: $model->is_active,
                description: $model->description,
                provisionedAt: $model->provisioned_at,
            ))->toArray();
    }

    /**
     * @return Client[]
     */
    public function findAllClients(): array
    {
        return ClientModel::query()
            ->get()
            ->map(fn (ClientModel $model) => new Client(
                clientId: new ClientId($model->id),
                name: $model->name,
                allowedScopes: AllowedScopes::fromStringArray($model->allowed_scopes),
                isActive: $model->is_active,
                description: $model->description,
                provisionedAt: $model->provisioned_at,
            ))->toArray();
    }

    public function deleteAllInactiveClients(): void
    {
        ClientModel::query()
            ->inactive()
            ->delete();
    }
}
