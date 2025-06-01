<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Services;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Enum;
use JuniorFontenele\LaravelVaultServer\Artifacts\NewClient;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Events\Client\ClientCreated;
use JuniorFontenele\LaravelVaultServer\Events\Client\ClientDeleted;
use JuniorFontenele\LaravelVaultServer\Events\Client\ClientTokenGenerated;
use JuniorFontenele\LaravelVaultServer\Events\Client\InactiveClientsCleanup;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotFoundException;
use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Queries\Client\ClientQueryBuilder;
use JuniorFontenele\LaravelVaultServer\Queries\Client\Filters\InactiveClientsFilter;

class ClientManagerService
{
    /**
     * Create a new client.
     *
     * @param string $name
     * @param string[] $allowedScopes
     * @param string $description
     * @return NewClient
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createClient(string $name, array $allowedScopes = [], string $description = ''): NewClient
    {
        $provisionToken = $this->generateProvisionToken();

        $validated = validator(
            [
                'name' => $name,
                'allowed_scopes' => $allowedScopes,
                'description' => $description,
                'provision_token' => $provisionToken,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'allowed_scopes' => ['required', 'array'],
                'allowed_scopes.*' => ['required', new Enum(Scope::class)],
                'description' => ['nullable', 'string', 'max:1000'],
                'provision_token' => ['required', 'string', 'size:32'],
            ]
        )->validate();

        $client = Client::create($validated);

        event(new ClientCreated($client));

        return new NewClient(
            $client,
            $provisionToken,
        );
    }

    /**
     * Reprovision a client.
     *
     * @param string $clientId Client ID
     * @return NewClient Containing the client and new provision token
     * @throws ClientNotFoundException
     */
    public function reprovisionClient(string $clientId): NewClient
    {
        $client = Client::find($clientId);

        if (is_null($client)) {
            throw new ClientNotFoundException($clientId);
        }

        $provisionToken = $this->generateProvisionToken();

        $client->provision_token = $provisionToken;
        $client->provisioned_at = null;
        $client->save();

        event(new ClientTokenGenerated($client));

        return new NewClient(
            $client,
            $provisionToken,
        );
    }

    /**
     * Delete a client.
     *
     * @param string $clientId Client ID
     * @return void
     * @throws ClientNotFoundException
     */
    public function deleteClient(string $clientId): void
    {
        $client = Client::find($clientId);

        if (is_null($client)) {
            throw new ClientNotFoundException($clientId);
        }

        $client->delete();

        event(new ClientDeleted($client->id));
    }

    /**
     * Cleanup inactive clients.
     *
     * @return Collection<string> The IDs of the deleted clients
     */
    public function cleanupInactiveClients(): Collection
    {
        $query = (new ClientQueryBuilder())
            ->addFilter(new InactiveClientsFilter())
            ->setSelectColumns(['id'])
            ->build();

        $inactiveClientsIds = $query->pluck('id');

        $query->delete();

        event(new InactiveClientsCleanup($inactiveClientsIds->toArray()));

        return $inactiveClientsIds;
    }

    /**
     * Get all clients.
     *
     * @return Collection<Client>
     */
    public function all(): Collection
    {
        return Client::all();
    }

    private function generateProvisionToken(): string
    {
        return bin2hex(random_bytes(length: 16));
    }
}
