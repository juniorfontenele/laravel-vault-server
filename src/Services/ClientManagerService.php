<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Services;

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Models\Client;

class ClientManagerService
{
    /**
     * Create a new client.
     *
     * @param string $name
     * @param array<int, string> $allowedScopes
     * @param string $description
     * @return Client
     */
    public function createClient(string $name, array $allowedScopes = [], string $description = ''): Client
    {
        $client = Client::create([
            'name' => $name,
            'allowed_scopes' => $allowedScopes,
            'description' => $description,
        ]);

        Event::dispatch('vault.client.created', [$client]);

        return $client;
    }

    public function generateProvisionToken(Client $client): string
    {
        $provisionToken = bin2hex(random_bytes(16));

        $client->provision_token = bcrypt($provisionToken);
        $client->save();

        Event::dispatch('vault.client.token.generated', [$client]);

        return $provisionToken;
    }

    /**
     * Delete a client.
     *
     * @param Client $client
     * @return bool
     */
    public function deleteClient(Client $client): bool
    {
        $client->delete();

        Event::dispatch('vault.client.deleted', [$client]);

        return true;
    }

    /**
     * Cleanup inactive clients.
     *
     * @return int
     */
    public function cleanupInactiveClients(): int
    {
        $deletedClients = Client::query()->inactive()->get();
        $countDeleted = $deletedClients->count();

        if ($countDeleted === 0) {
            return 0;
        }

        Client::query()->inactive()->delete();

        Event::dispatch('vault.client.cleanup', [$deletedClients]);

        return $deletedClients->count();
    }
}
