<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Facades\Hash;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Entities\Client;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Repositories\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Client\ValueObjects\AllowedScopes;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\ValueObjects\Id;
use JuniorFontenele\LaravelVaultServer\Models\Client as ClientModel;

class EloquentClientRepository implements ClientRepositoryInterface
{
    public function findById(string $clientId): ?Client
    {
        $model = ClientModel::query()->find($clientId);

        if (! $model) {
            return null;
        }

        return new Client(
            id: new Id($model->id),
            name: $model->name,
            allowedScopes: AllowedScopes::fromStringArray($model->allowed_scopes),
            isActive: $model->is_active,
            description: $model->description,
            provisionedAt: $model->provisioned_at,
        );
    }

    public function save(Client $clientEntity): void
    {
        $model = ClientModel::query()->find($clientEntity->id()) ?? new ClientModel();

        $model->id = $clientEntity->id();
        $model->name = $clientEntity->name;
        $model->allowed_scopes = $clientEntity->allowedScopes->toArray();
        $model->description = $clientEntity->description;
        $model->provision_token = $clientEntity->isNotProvisioned() ? Hash::make($clientEntity->provisionToken()) : null;
        $model->provisioned_at = $clientEntity->isNotProvisioned() ? null : $clientEntity->provisionedAt;
        $model->save();
    }

    public function delete(Client $clientEntity): void
    {
        ClientModel::query()->where('id', $clientEntity->id())->delete();
    }

    /**
     * @return Client[]
     */
    public function findAllInactive(): array
    {
        return ClientModel::query()
            ->inactive()
            ->get()
            ->map(fn (ClientModel $model) => new Client(
                id: new Id($model->id),
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
    public function findAllActive(): array
    {
        return ClientModel::query()
            ->active()
            ->get()
            ->map(fn (ClientModel $model) => new Client(
                id: new Id($model->id),
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
    public function findAll(): array
    {
        return ClientModel::query()
            ->get()
            ->map(fn (ClientModel $model) => new Client(
                id: new Id($model->id),
                name: $model->name,
                allowedScopes: AllowedScopes::fromStringArray($model->allowed_scopes),
                isActive: $model->is_active,
                description: $model->description,
                provisionedAt: $model->provisioned_at,
            ))->toArray();
    }

    public function deleteAllInactive(): void
    {
        ClientModel::query()
            ->inactive()
            ->delete();
    }
}
