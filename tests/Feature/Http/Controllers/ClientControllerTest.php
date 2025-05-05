<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Feature\Http\Controllers;

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;

class ClientControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadWorkbenchMigrations = true;

        Event::fake();
    }

    public function testProvisionClient(): void
    {
        // Create a client first
        $client = VaultClientManager::createClient(
            name: 'Test Client',
            allowedScopes: ['keys:read', 'hashes:read']
        );

        $response = $this->postJson(route('vault.client.provision', [$client->clientId]), [
            'provision_token' => $client->provisionToken,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'key_id',
                'client_id',
                'public_key',
                'private_key',
                'version',
                'valid_from',
                'valid_until',
            ]);

        Event::assertDispatched('vault.client.provisioned');
    }

    public function testProvisionClientWithInvalidToken(): void
    {
        // Create a client first
        $client = VaultClientManager::createClient(
            name: 'Test Client',
            allowedScopes: ['keys:read', 'hashes:read']
        );

        $response = $this->postJson(route('vault.client.provision', [$client->clientId]), [
            'provision_token' => 'invalid-token',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error']);
    }

    public function testProvisionClientWithNonExistingClient(): void
    {
        $response = $this->postJson(route('vault.client.provision', ['non-existent-client']), [
            'provision_token' => 'some-token',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error']);
    }

    public function testProvisionClientWithoutToken(): void
    {
        // Create a client first
        $client = VaultClientManager::createClient(
            name: 'Test Client',
            allowedScopes: ['keys:read', 'hashes:read']
        );

        $response = $this->postJson(route('vault.client.provision', [$client->clientId]), []);

        $response->assertStatus(422)
            ->assertJsonStructure(['error']);
    }
}
