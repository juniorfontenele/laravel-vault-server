<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Interfaces\Console\Commands;

use Illuminate\Console\Command;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Persistence\Models\ClientModel;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Persistence\Models\KeyModel;

use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class VaultKeyManager extends Command
{
    protected $signature = 'vault:key
        {action? : Action to perform (generate|rotate|list|delete|revoke|cleanup)}
        {--client= : Client UUID}
        {--key-id= : Key ID (for delete)}
        {--valid-days= : Validity period in days}
    ';

    protected $description = 'Vault Key Management';

    public function handle()
    {
        $action = $this->getAction();

        match ($action) {
            'generate' => $this->generate(),
            'rotate' => $this->rotate(),
            'list' => $this->listKeys(),
            'delete' => $this->deleteKey(),
            'revoke' => $this->revokeKey(),
            'cleanup' => $this->cleanupKeys(),
            default => $this->error("Action '{$action}' is not supported."),
        };
    }

    protected function getAction(): string
    {
        $action = $this->argument('action') ?? select(
            'Select an action',
            [
                'generate' => 'Create a new key pair',
                'rotate' => 'Rotate an existing key pair',
                'list' => 'List all keys for a client',
                'delete' => 'Delete a key pair',
                'revoke' => 'Revoke a key pair',
                'cleanup' => 'Cleanup expired and revoked keys',
            ],
            required: true,
        );

        return $action;
    }

    protected function getClient(): ClientModel
    {
        if (ClientModel::count() === 0) {
            $this->error('No clients found. Please create a client first.');

            exit(static::FAILURE);
        }

        $clientUuid = $this->option('client') ?? search(
            label: 'Search for a client',
            options: fn (string $value) => ClientModel::query()
                ->where('id', 'like', "%{$value}%")
                ->orWhere('name', 'like', "%{$value}%")
                ->get()
                ->mapWithKeys(fn ($client) => [$client->id => "{$client->name} - {$client->id}"])
                ->toArray(),
            required: true,
        );

        $client = ClientModel::where('id', $clientUuid)->first();

        if (! $client) {
            $this->error("Client with UUID {$clientUuid} not found.");

            exit(static::FAILURE);
        }

        return $client;
    }

    protected function generate()
    {
        $client = $this->getClient();

        $days = $this->option('valid-days') ?? text(
            label: 'Validity period in days',
            default: '365',
            required: true,
        );

        if (! is_numeric($days) || (int) $days <= 0) {
            return $this->error('Invalid number of days');
        }

        [$key, $privateKey] = VaultKey::createKeyForClient(
            client: $client,
            expiresIn: (int) $days,
        );

        $this->info("Key pair generated successfully.");
        $this->line("KID: {$key->id}");
        $this->line("Public Key:");
        $this->line($key->public_key);
        $this->line("Private Key:");
        $this->line($privateKey);
        $this->warn("Keep the private key safe!");

        exit(static::SUCCESS);
    }

    protected function rotate()
    {
        $client = $this->getClient();

        $key = $this->getKeyForClient($client);

        [$newKey, $privateKey] = VaultKey::rotate(
            key: $key,
            expiresIn: (int) $this->option('valid-days') ?? 365,
        );

        $this->info("Key pair rotated successfully.");
        $this->line("KID: {$newKey->id}");
        $this->line("Public Key:");
        $this->line($newKey->public_key);
        $this->line("Private Key:");
        $this->line($privateKey);
        $this->warn("Keep the private key safe!");

        exit(static::SUCCESS);
    }

    protected function listKeys()
    {
        $client = $this->getClient();

        $keys = $client->keys;

        if ($keys->isEmpty()) {
            $this->info('No keys found for this client.');

            return;
        }

        $this->table(['ID', 'Public Key', 'Revoked?', 'Valid From', 'Valid Until'], $keys->map(function ($key) {
            return [
                'id' => $key->id,
                'public_key' => $key->public_key,
                'revoked' => $key->revoked ? '✅' : '❌',
                'valid_from' => $key->valid_from,
                'valid_until' => $key->valid_until,
            ];
        })->toArray());

        exit(static::SUCCESS);
    }

    protected function getKeyForClient(ClientModel $client): KeyModel
    {
        $keys = $client->keys()->valid()->get();

        if ($keys->isEmpty()) {
            $this->info('No valid keys found for this client.');

            exit(static::FAILURE);
        }

        $keyId = $this->option('key-id') ?? search(
            label: 'Select a key to delete',
            options: fn () => $keys->mapWithKeys(
                fn ($key) => [$key->id => "{$key->id} (Expires: {$key->valid_until->format('Y-m-d')})"],
            )->toArray(),
            required: true,
        );

        $key = $keys->where('id', $keyId)->first();

        if (! $key) {
            $this->error('Key not found');

            exit(static::FAILURE);
        }

        return $key;
    }

    protected function deleteKey()
    {
        $client = $this->getClient();

        $key = $this->getKeyForClient($client);

        $key->delete();

        $this->info("Key with ID {$key->id} deleted successfully.");

        exit(static::SUCCESS);
    }

    protected function revokeKey()
    {
        $client = $this->getClient();

        $key = $this->getKeyForClient($client);

        $key->revoke();

        $this->info("Key with ID {$key->id} revoked successfully.");

        exit(static::SUCCESS);
    }

    protected function cleanupKeys(): void
    {
        $expiredCount = VaultKey::cleanupExpiredKeys();

        if ($expiredCount === 0) {
            $this->info('No expired keys found.');
        } else {
            $this->info("{$expiredCount} expired key(s) removed successfully.");
        }

        $revokedCount = VaultKey::cleanupRevokedKeys();

        if ($revokedCount === 0) {
            $this->info('No revoked keys found.');
        } else {
            $this->info("{$revokedCount} revoked key(s) removed successfully.");
        }

        exit(static::SUCCESS);
    }
}
