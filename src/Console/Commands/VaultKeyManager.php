<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Models\Client;

use JuniorFontenele\LaravelVaultServer\Models\Key;
use JuniorFontenele\LaravelVaultServer\Queries\Client\ClientQueryBuilder;
use JuniorFontenele\LaravelVaultServer\Queries\Client\Filters\ActiveClientsFilter;
use JuniorFontenele\LaravelVaultServer\Queries\Key\Filters\ByClientId;
use JuniorFontenele\LaravelVaultServer\Queries\Key\Filters\NonRevoked;
use JuniorFontenele\LaravelVaultServer\Queries\Key\KeyQueryBuilder;

use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class VaultKeyManager extends Command
{
    protected $signature = 'vault-server:key
        {action? : Action to perform (generate|rotate|list|revoke|cleanup)}
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
                'revoke' => 'Revoke a key pair',
                'cleanup' => 'Cleanup expired and revoked keys',
            ],
            required: true,
        );

        return $action;
    }

    protected function getClient(): Client
    {
        /** @var Collection<Client> $clients */
        $clients = (new ClientQueryBuilder())
            ->addFilter(new ActiveClientsFilter())
            ->build()
            ->get();

        if ($clients->isEmpty()) {
            $this->error('No active clients found.');

            exit(static::FAILURE);
        }

        $clientUuid = $this->option('client') ?? search(
            label: 'Search for a client',
            options: fn (string $value) => $clients
                ->filter(function (Client $client) use ($value): bool {
                    return str_contains($client->name, $value)
                    || str_contains($client->id, $value);
                })
                ->mapWithKeys(
                    fn (Client $client) => [$client->id => "{$client->name} ({$client->id})"],
                )
                ->toArray(),
            required: true,
        );

        $client = $clients->where('id', '=', $clientUuid)->first();

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

        $newKey = VaultKey::create(
            clientId: $client->id,
            keySize: 2048,
            expiresIn: (int) $days,
        );

        $this->info("Key pair generated successfully.");
        $this->line("KID: {$newKey->key->id}");
        $this->line("Public Key:");
        $this->line($newKey->key->public_key);
        $this->line("Private Key:");
        $this->line($newKey->private_key);
        $this->warn("Keep the private key safe!");

        exit(static::SUCCESS);
    }

    protected function rotate()
    {
        $this->generate();
    }

    protected function listKeys()
    {
        $client = $this->getClient();

        $keys = $this->getAllKeysForClientId($client->id);

        if ($keys->isEmpty()) {
            $this->info('No keys found for this client.');

            return;
        }

        $this->table(['ID', 'Public Key', 'Revoked?', 'Valid From', 'Valid Until'], $keys->map(function (Key $keyModel): array {
            return [
                'id' => $keyModel->id,
                'public_key' => $keyModel->public_key,
                'is_revoked' => $keyModel->is_revoked ? '✅' : '❌',
                'valid_from' => $keyModel->valid_from,
                'valid_until' => $keyModel->valid_until,
            ];
        })->toArray());

        exit(static::SUCCESS);
    }

    /**
     * Get all keys for a specific client ID.
     *
     * @param string $clientId
     * @return Collection<Key>
     */
    private function getAllKeysForClientId(string $clientId): Collection
    {
        return (new KeyQueryBuilder())
            ->addFilter(new ByClientId($clientId))
            ->build()
            ->get();
    }

    /**
     * Get a key model by client ID.
     *
     * @param string $clientId
     * @return Key|null
     */
    private function getActiveKeyForClientId(string $clientId): ?Key
    {
        return (new KeyQueryBuilder())
            ->addFilter(new ByClientId($clientId))
            ->addFilter(new NonRevoked())
            ->build()
            ->first();
    }

    protected function revokeKey()
    {
        $client = $this->getClient();

        $key = $this->getActiveKeyForClientId($client->id);

        if (!$key instanceof \JuniorFontenele\LaravelVaultServer\Models\Key) {
            $this->error("Key not found for client ID {$client->id}.");

            exit(static::FAILURE);
        }

        VaultKey::revoke($key->id);

        $this->info("Key with ID {$key->id} revoked successfully.");

        exit(static::SUCCESS);
    }

    protected function cleanupKeys(): void
    {
        $expiredKeys = VaultKey::cleanupExpiredKeys();

        if ($expiredKeys->count() === 0) {
            $this->info('No expired keys found.');
        } else {
            $this->info("{$expiredKeys->count()} expired key(s) removed successfully.");
        }

        $revokedKeys = VaultKey::cleanupRevokedKeys();

        if ($revokedKeys->count() === 0) {
            $this->info('No revoked keys found.');
        } else {
            $this->info("{$revokedKeys->count()} revoked key(s) removed successfully.");
        }

        exit(static::SUCCESS);
    }
}
