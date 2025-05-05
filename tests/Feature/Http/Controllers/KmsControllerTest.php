<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Feature\Http\Controllers;

use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;

class KmsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadWorkbenchMigrations = true;

        $this->updateAuthorizationHeaders();
    }

    private function createClientAndProvision(): array
    {
        $client = VaultClientManager::createClient(
            name: 'Test Client',
            allowedScopes: ['keys:read', 'keys:rotate']
        );

        $response = $this->postJson(route('vault.client.provision', [$client->clientId]), [
            'provision_token' => $client->provisionToken,
        ]);

        $keyData = $response->json();

        return [
            'client' => $client,
            'key' => $keyData,
        ];
    }

    public function testShowKey(): void
    {
        $data = $this->createClientAndProvision();

        $response = $this->getJson(route('vault.kms.get', [$data['key']['key_id']]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'key_id',
                'client_id',
                'public_key',
                'version',
                'valid_from',
                'valid_until',
                'is_revoked',
            ])
            ->assertJson([
                'key_id' => $data['key']['key_id'],
                'client_id' => $data['client']->clientId,
                'is_revoked' => false,
            ]);
    }

    public function testRotateKey(): void
    {
        $data = $this->createClientAndProvision();

        $response = $this->postJson(route('vault.kms.rotate', [$data['key']['key_id']]));

        $response->assertStatus(201)
            ->assertJsonStructure([
                'key_id',
                'client_id',
                'public_key',
                'version',
                'valid_from',
                'valid_until',
            ])
            ->assertJson([
                'client_id' => $data['client']->clientId,
                'version' => 2, // Version should be incremented
            ]);

        // The original key should now be revoked
        $checkOldKey = $this->getJson(uri: route('vault.kms.get', [$data['key']['key_id']]));
        $checkOldKey->assertStatus(200)
            ->assertJson([
                'key_id' => $data['key']['key_id'],
                'is_revoked' => true,
            ]);
    }

    public function testShowKeyWithNonExistingKey(): void
    {
        $response = $this->getJson(route('vault.kms.get', ['non-existent-key']));

        $response->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function testRotateKeyWithNonExistingKey(): void
    {
        $response = $this->postJson(route('vault.kms.rotate', ['non-existent-key']));

        $response->assertStatus(404)
            ->assertJsonStructure(['message']);
    }
}
