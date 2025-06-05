<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Models\Client;

use function Pest\Faker\fake;

describe('VaultClientManagement Command', function () {
    it('shows no clients found when there are no clients in list command', function () {
        $this->artisan('vault-server:client', ['action' => 'list'])
            ->assertExitCode(0)
            ->expectsOutput('No clients found.');
    });

    it('shows clients table when clients exist', function () {
        $client = Client::factory()->create();

        $this->artisan('vault-server:client', ['action' => 'list'])
            ->assertExitCode(0)
            ->expectsTable(
                ['ID', 'Name', 'Provisioned', 'Scopes'],
                [
                    [
                        $client->id,
                        $client->name,
                        '❌',
                        implode(', ', $client->allowed_scopes),
                    ],
                ]
            );
    });

    it('can create client through command line', function () {
        $name = 'Test Client';
        $description = 'This is a test client';
        $scopes = implode(',', [Scope::KEYS_READ->value, Scope::KEYS_ROTATE->value]);

        $this->artisan('vault-server:client', [
            'action' => 'create',
            '--name' => $name,
            '--description' => $description,
            '--scopes' => $scopes,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain("Client '{$name}' created successfully.")
            ->expectsOutputToContain('Client ID: ')
            ->expectsOutputToContain('Provision Token: ');

        $client = Client::where('name', $name)->first();
        expect($client)->not->toBeNull();
        expect($client->description)->toBe($description);
        expect($client->allowed_scopes)->toBe(explode(',', $scopes));
    });

    it('can create client through questions', function () {
        $name = 'Test Client';
        $description = 'This is a test client';
        $scopes = [Scope::KEYS_READ->value, Scope::KEYS_ROTATE->value];

        $this->artisan('vault-server:client')
            ->expectsQuestion('Select an action', 'create')
            ->expectsQuestion('What is the client\'s name?', $name)
            ->expectsQuestion('What is the client\'s description?', $description)
            ->expectsQuestion(
                'Allowed scopes',
                $scopes
            )
            ->expectsOutputToContain("Client '{$name}' created successfully.")
            ->expectsOutputToContain('Client ID: ')
            ->expectsOutputToContain('Provision Token: ')
            ->assertExitCode(0);

        $client = Client::where('name', $name)->first();
        expect($client)->not->toBeNull();
        expect($client->description)->toBe($description);
        expect($client->allowed_scopes)->toBe($scopes);
    });

    it('cannot reprovision inexistent client', function () {
        Client::factory()->create();

        $this->artisan('vault-server:client', [
            'action' => 'provision',
            '--client' => 'nonexistent-client',
        ])
            ->assertExitCode(1)
            ->expectsOutput('Client with UUID nonexistent-client not found.');
    });

    it('can reprovision existing client', function () {
        $newClient = VaultClientManager::createClient(fake()->word(), [
            Scope::KEYS_READ->value,
            Scope::KEYS_ROTATE->value,
        ]);

        VaultClientManager::provisionClient($newClient->client->id, $newClient->plaintext_provision_token);

        $this->artisan('vault-server:client', [
            'action' => 'provision',
            '--client' => $newClient->client->id,
        ])
            ->assertExitCode(0)
            ->expectsOutput("Client ID: {$newClient->client->id}")
            ->expectsOutputToContain('Provision Token: ');

        $newClient->client->refresh();
        expect($newClient->client->provisioned_at)->toBeNull()
            ->and($newClient->client->provision_token)->not->toBeNull();
    });

    it('cannot delete inexistent client', function () {
        Client::factory()->create();

        $this->artisan('vault-server:client', [
            'action' => 'delete',
            '--client' => 'nonexistent-client',
        ])
            ->assertExitCode(1)
            ->expectsOutput('Client with UUID nonexistent-client not found.');
    });

    it('can delete existing client', function () {
        $client = Client::factory()->create();

        $this->artisan('vault-server:client', [
            'action' => 'delete',
            '--client' => $client->id,
        ])
            ->assertExitCode(0)
            ->expectsOutput("Client with UUID {$client->id} deleted successfully.");

        expect(Client::find($client->id))->toBeNull();
    });

    it('cleanups inactive clients', function () {
        $activeClient = Client::factory()->create(['is_active' => true]);
        $inactiveClient = Client::factory()->create(['is_active' => false]);

        $this->artisan('vault-server:client', ['action' => 'cleanup'])
            ->assertExitCode(0)
            ->expectsOutput("Deleted 1 inactive clients.");

        expect(Client::find($activeClient->id))->not->toBeNull();
        expect(Client::find($inactiveClient->id))->toBeNull();
    });

    it('shows error for unsupported action', function () {
        $this->artisan('vault-server:client', ['action' => 'unsupported'])
            ->assertExitCode(1)
            ->expectsOutput("Action 'unsupported' not supported.");
    });
});
