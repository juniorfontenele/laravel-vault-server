<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Models\Key;

describe('VaultKeyManager Command', function () {
    it('shows error for unsupported action', function () {
        $this->artisan('vault-server:key', ['action' => 'unsupported'])
            ->expectsOutput("Action 'unsupported' is not supported.")
            ->assertExitCode(1);
    });

    describe('generate action', function () {
        it('can generate key through command line options', function () {
            $client = Client::factory()->create(['is_active' => true]);

            $this->artisan('vault-server:key', [
                'action' => 'generate',
                '--client' => $client->id,
                '--valid-days' => '180',
            ])
                ->expectsOutput('Key pair generated successfully.')
                ->expectsOutputToContain('KID: ')
                ->expectsOutputToContain('Public Key:')
                ->expectsOutputToContain('Private Key:')
                ->expectsOutput('Keep the private key safe!')
                ->assertExitCode(0);

            expect(Key::where('client_id', $client->id)->count())->toBe(1);
        });

        it('shows error for invalid number of days', function () {
            $client = Client::factory()->create(['is_active' => true]);

            $this->artisan('vault-server:key', [
                'action' => 'generate',
                '--client' => $client->id,
                '--valid-days' => 'invalid',
            ])
                ->expectsOutput('Invalid number of days')
                ->assertExitCode(1);

            expect(Key::where('client_id', $client->id)->count())->toBe(0);
        });

        it('shows error for zero days', function () {
            $client = Client::factory()->create(['is_active' => true]);

            $this->artisan('vault-server:key', [
                'action' => 'generate',
                '--client' => $client->id,
                '--valid-days' => '0',
            ])
                ->expectsOutput('Invalid number of days')
                ->assertExitCode(1);

            expect(Key::where('client_id', $client->id)->count())->toBe(0);
        });

        it('shows error for negative days', function () {
            $client = Client::factory()->create(['is_active' => true]);

            $this->artisan('vault-server:key', [
                'action' => 'generate',
                '--client' => $client->id,
                '--valid-days' => '-10',
            ])
                ->expectsOutput('Invalid number of days')
                ->assertExitCode(1);

            expect(Key::where('client_id', $client->id)->count())->toBe(0);
        });
    });

    describe('rotate action', function () {
        it('can rotate key through command line options', function () {
            $client = Client::factory()->create(['is_active' => true]);
            $existingKey = Key::factory()->create(['client_id' => $client->id]);

            $this->artisan('vault-server:key', [
                'action' => 'rotate',
                '--client' => $client->id,
                '--valid-days' => '180',
            ])
                ->expectsOutput('Key pair generated successfully.')
                ->expectsOutputToContain('KID: ')
                ->expectsOutputToContain('Public Key:')
                ->expectsOutputToContain('Private Key:')
                ->expectsOutput('Keep the private key safe!')
                ->assertExitCode(0);

            expect(Key::where('client_id', $client->id)->count())->toBe(2);

            $existingKey->refresh();
            expect($existingKey->is_revoked)->toBeTrue();
        });
    });

    describe('list action', function () {
        it('can list keys for a client through command line options', function () {
            $client = Client::factory()->create(['is_active' => true]);
            $key1 = Key::factory()->create([
                'client_id' => $client->id,
                'is_revoked' => false,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addYear(),
            ]);
            $key2 = Key::factory()->create([
                'client_id' => $client->id,
                'is_revoked' => true,
                'valid_from' => now()->subMonth(),
                'valid_until' => now()->addYear(),
            ]);

            $this->artisan('vault-server:key', [
                'action' => 'list',
                '--client' => $client->id,
            ])
                ->expectsTable(
                    ['ID', 'Public Key', 'Revoked?', 'Valid From', 'Valid Until'],
                    [
                        [
                            $key1->id,
                            $key1->public_key,
                            '❌',
                            $key1->valid_from,
                            $key1->valid_until,
                        ],
                        [
                            $key2->id,
                            $key2->public_key,
                            '✅',
                            $key2->valid_from,
                            $key2->valid_until,
                        ],
                    ]
                )
                ->assertExitCode(0);
        });

        it('shows message when no keys found for client', function () {
            $client = Client::factory()->create(['is_active' => true]);

            $this->artisan('vault-server:key', [
                'action' => 'list',
                '--client' => $client->id,
            ])
                ->expectsOutput('No keys found for this client.')
                ->assertExitCode(0);
        });
    });

    describe('revoke action', function () {
        it('can revoke key through command line options', function () {
            $client = Client::factory()->create(['is_active' => true]);
            $key = Key::factory()->create([
                'client_id' => $client->id,
                'is_revoked' => false,
            ]);

            $this->artisan('vault-server:key', [
                'action' => 'revoke',
                '--client' => $client->id,
            ])
                ->expectsOutput("Key with ID {$key->id} revoked successfully.")
                ->assertExitCode(0);

            $key->refresh();
            expect($key->is_revoked)->toBeTrue();
        });

        it('shows error when no active key found for client', function () {
            $client = Client::factory()->create(['is_active' => true]);
            Key::factory()->create([
                'client_id' => $client->id,
                'is_revoked' => true,
            ]);

            $this->artisan('vault-server:key', [
                'action' => 'revoke',
                '--client' => $client->id,
            ])
                ->expectsOutput("Key not found for client ID {$client->id}.")
                ->assertExitCode(0);
        });

        it('shows error when no keys exist for client', function () {
            $client = Client::factory()->create(['is_active' => true]);

            $this->artisan('vault-server:key', [
                'action' => 'revoke',
                '--client' => $client->id,
            ])
                ->expectsOutput("Key not found for client ID {$client->id}.")
                ->assertExitCode(0);
        });
    });

    describe('cleanup action', function () {
        it('cleans up expired and revoked keys', function () {
            $client = Client::factory()->create();

            Key::factory()->create(['client_id' => $client->id, 'valid_until' => now()->subDay()]);
            Key::factory()->create(['client_id' => $client->id, 'valid_until' => now()->subHour()]);
            Key::factory()->create(['client_id' => $client->id, 'is_revoked' => true]);
            Key::factory()->create(['client_id' => $client->id, 'is_revoked' => true]);
            Key::factory()->create(['client_id' => $client->id, 'valid_until' => now()->addYear()]);

            $this->artisan('vault-server:key', ['action' => 'cleanup'])
                ->expectsOutput('2 expired key(s) removed successfully.')
                ->expectsOutput('2 revoked key(s) removed successfully.')
                ->assertExitCode(0);
        });

        it('shows message when no expired keys found', function () {
            $client = Client::factory()->create();

            Key::factory()->create(['client_id' => $client->id, 'valid_until' => now()->addYear()]);
            Key::factory()->create(['client_id' => $client->id, 'is_revoked' => true]);

            $this->artisan('vault-server:key', ['action' => 'cleanup'])
                ->expectsOutput('No expired keys found.')
                ->expectsOutput('1 revoked key(s) removed successfully.')
                ->assertExitCode(0);
        });

        it('shows message when no revoked keys found', function () {
            $client = Client::factory()->create();

            Key::factory()->create(['client_id' => $client->id, 'valid_until' => now()->subDay()]);
            Key::factory()->create(['client_id' => $client->id, 'valid_until' => now()->addYear()]);

            $this->artisan('vault-server:key', ['action' => 'cleanup'])
                ->expectsOutput('1 expired key(s) removed successfully.')
                ->expectsOutput('No revoked keys found.')
                ->assertExitCode(0);
        });

        it('shows message when no keys need cleanup', function () {
            $client = Client::factory()->create();

            Key::factory()->create(['client_id' => $client->id, 'valid_until' => now()->addYear()]);

            $this->artisan('vault-server:key', ['action' => 'cleanup'])
                ->expectsOutput('No expired keys found.')
                ->expectsOutput('No revoked keys found.')
                ->assertExitCode(0);
        });

        it('works when no keys exist', function () {
            $this->artisan('vault-server:key', ['action' => 'cleanup'])
                ->expectsOutput('No expired keys found.')
                ->expectsOutput('No revoked keys found.')
                ->assertExitCode(0);
        });
    });
});
