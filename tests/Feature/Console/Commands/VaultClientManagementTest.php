<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Feature\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;

class VaultClientManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadWorkbenchMigrations = true;
    }

    public function testCreateClientCommand(): void
    {
        $this->artisan('vault:client', [
            'action' => 'create',
            '--name' => 'Test Client',
            '--description' => 'Test Description',
            '--scopes' => 'keys:read,hashes:read',
        ])
            ->expectsOutput('Client \'Test Client\' created successfully.')
            ->expectsOutputToContain('Client ID:')
            ->expectsOutputToContain('Provision Token:')
            ->assertExitCode(0);

        // Verify client was created
        $clients = VaultClientManager::getAllClients();
        $this->assertCount(1, $clients);
        $this->assertEquals('Test Client', $clients[0]->name);
        $this->assertEquals('Test Description', $clients[0]->description);
        $this->assertEquals(['keys:read', 'hashes:read'], $clients[0]->allowedScopes);
    }

    public function testListClientsCommand(): void
    {
        // Create some clients first
        VaultClientManager::createClient(
            name: 'Test Client 1',
            allowedScopes: ['keys:read'],
            description: 'CreateClient1',
        );

        VaultClientManager::createClient(
            name: 'Test Client 2',
            allowedScopes: ['hashes:read'],
            description: 'CreateClient2',
        );

        $this->withoutMockingConsoleOutput();
        $this->artisan('vault:client', [
            'action' => 'list',
        ]);

        $output = Artisan::output();
        $this->assertStringContainsString('Test Client 1', $output);
        $this->assertStringContainsString('Test Client 2', $output);
        $this->assertStringContainsString('CreateClient1', $output);
        $this->assertStringContainsString('CreateClient2', $output);
        $this->assertStringContainsString('keys:read', $output);
        $this->assertStringContainsString('hashes:read', $output);
    }

    public function testDeleteClientCommand(): void
    {
        // Create a client first
        $client = VaultClientManager::createClient(
            name: 'Test Client for Deletion',
            allowedScopes: ['keys:read']
        );

        $this->artisan('vault:client', [
            'action' => 'delete',
            '--client' => $client->clientId,
        ])
            ->expectsOutput("Client with UUID {$client->clientId} deleted successfully.")
            ->assertExitCode(0);

        // Verify client was deleted
        $clients = VaultClientManager::getAllClients();
        $this->assertCount(0, $clients);
    }

    public function testProvisionClientCommand(): void
    {
        // Create a client first
        $client = VaultClientManager::createClient(
            name: 'Test Client for Provision',
            allowedScopes: ['keys:read']
        );

        // Provision and get token
        $this->artisan('vault:client', [
            'action' => 'provision',
            '--client' => $client->clientId,
        ])
            ->expectsOutput("Client ID: {$client->clientId}")
            ->expectsOutputToContain('Provision Token:')
            ->assertExitCode(0);
    }

    public function testCleanupClientsWithNoInactiveClients(): void
    {
        $this->artisan('vault:client', [
            'action' => 'cleanup',
        ])
            ->expectsOutput('No inactive clients found.')
            ->assertExitCode(0);
    }
}
