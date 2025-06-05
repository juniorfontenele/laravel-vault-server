<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Models\Client;

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
});
