<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Feature;

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\ClientException;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;

class ClientManagerServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadWorkbenchMigrations = true;

        Event::fake();
    }

    public function testCreateClient(): void
    {
        $response = VaultClientManager::createClient(
            name: 'Test Client',
            allowedScopes: ['keys:read', 'hashes:read'],
            description: 'Test description'
        );

        $this->assertInstanceOf(CreateClientResponseDTO::class, $response);
        $this->assertEquals('Test Client', $response->name);
        $this->assertEquals(['keys:read', 'hashes:read'], $response->allowedScopes);
        $this->assertEquals('Test description', $response->description);
        $this->assertNotEmpty($response->clientId);
        $this->assertNotEmpty($response->provisionToken);

        Event::assertDispatched('vault.client.created');
    }

    public function testGenerateProvisionToken(): void
    {
        // Create a client first
        $client = VaultClientManager::createClient(
            name: 'Test Client for Token',
            allowedScopes: ['keys:read']
        );

        Event::fake(); // Reset event fake to test just the token generation event

        $provisionToken = VaultClientManager::generateProvisionToken($client->clientId);

        $this->assertNotEmpty($provisionToken);
        $this->assertNotEquals($client->provisionToken, $provisionToken);

        Event::assertDispatched('vault.client.token.generated');
    }

    public function testGenerateProvisionTokenWithNonExistingClientThrowsException(): void
    {
        $this->expectException(ClientException::class);

        VaultClientManager::generateProvisionToken('non-existent-client-id');
    }

    public function testDeleteClient(): void
    {
        // Create a client first
        $client = VaultClientManager::createClient(
            name: 'Test Client for Deletion',
            allowedScopes: ['keys:read']
        );

        Event::fake(); // Reset event fake to test just the deletion event

        VaultClientManager::deleteClient($client->clientId);

        Event::assertDispatched('vault.client.deleted');

        // Verify client was deleted by trying to generate a token for it
        $this->expectException(ClientException::class);
        VaultClientManager::generateProvisionToken($client->clientId);
    }

    public function testGetAllClients(): void
    {
        // Create some clients first
        VaultClientManager::createClient(
            name: 'Test Client 1',
            allowedScopes: ['keys:read']
        );

        VaultClientManager::createClient(
            name: 'Test Client 2',
            allowedScopes: ['hashes:read']
        );

        $clients = VaultClientManager::getAllClients();

        $this->assertCount(2, $clients);
        $this->assertEquals('Test Client 1', $clients[0]->name);
        $this->assertEquals('Test Client 2', $clients[1]->name);
    }
}
