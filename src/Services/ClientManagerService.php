<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Services;

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\CreateClient;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\DeleteClient;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\DeleteInactiveClients;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\ReprovisionClient;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Exceptions\ClientException;

class ClientManagerService
{
    /**
     * Create a new client.
     *
     * @param string $name
     * @param string[] $allowedScopes
     * @param string $description
     * @return CreateClientResponseDTO
     */
    public function createClient(string $name, array $allowedScopes = [], string $description = ''): CreateClientResponseDTO
    {
        $createClient = app(CreateClient::class);

        $clientDTO = new CreateClientDTO(
            name: $name,
            allowedScopes: $allowedScopes,
            description: $description,
        );

        $client = $createClient->execute($clientDTO);

        Event::dispatch('vault.client.created', [$client]);

        return $client;
    }

    /**
     * Reprovision a client.
     *
     * @param string $clientId Client ID
     * @return string Provision token
     * @throws ClientException
     */
    public function generateProvisionToken(string $clientId): string
    {
        $reprovision = app(ReprovisionClient::class);

        $client = $reprovision->execute($clientId);

        Event::dispatch('vault.client.token.generated', [$client]);

        return $client->provisionToken;
    }

    /**
     * Delete a client.
     *
     * @param string $clientId Client ID
     * @return void
     */
    public function deleteClient(string $clientId): void
    {
        $deleteClient = app(DeleteClient::class);

        $deleteClient->execute($clientId);

        Event::dispatch('vault.client.deleted', [$clientId]);
    }

    /**
     * Cleanup inactive clients.
     *
     * @return int
     */
    public function cleanupInactiveClients(): int
    {
        $deleteInactiveClients = app(DeleteInactiveClients::class);

        $deletedClients = $deleteInactiveClients->execute();

        Event::dispatch('vault.client.cleanup', [$deletedClients]);

        return count($deletedClients);
    }
}
