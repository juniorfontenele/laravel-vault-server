<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Services;

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\ClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\CreateClientUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\DeleteClientUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\DeleteInactiveClientsUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\FindAllClientsUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\ReprovisionClientUseCase;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\ClientException;

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
        $clientDTO = new CreateClientDTO(
            name: $name,
            allowedScopes: $allowedScopes,
            description: $description,
        );

        $client = app(CreateClientUseCase::class)->execute($clientDTO);

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
        $client = app(ReprovisionClientUseCase::class)->execute($clientId);

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
        app(DeleteClientUseCase::class)->execute($clientId);

        Event::dispatch('vault.client.deleted', [$clientId]);
    }

    /**
     * Cleanup inactive clients.
     *
     * @return int
     */
    public function cleanupInactiveClients(): int
    {
        $deletedClients = app(DeleteInactiveClientsUseCase::class)->execute();

        Event::dispatch('vault.client.cleanup', [$deletedClients]);

        return count($deletedClients);
    }

    /**
     * Get all clients.
     *
     * @return ClientResponseDTO[]
     */
    public function getAllClients(): array
    {
        return app(FindAllClientsUseCase::class)->execute();
    }
}
