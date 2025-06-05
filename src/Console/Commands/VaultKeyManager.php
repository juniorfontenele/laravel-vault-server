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

    public function handle(): int
    {
        $action = $this->getAction();

        return match ($action) {
            'generate' => $this->generate(),
            'rotate' => $this->rotate(),
            'list' => $this->listKeys(),
            'revoke' => $this->revokeKey(),
            'cleanup' => $this->cleanupKeys(),
            default => (function () use ($action) {
                $this->error("Action '{$action}' is not supported.");

                return static::FAILURE;
            })(),
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

    protected function getClient(): ?Client
    {
        /** @var Collection<Client> $clients */
        $clients = (new ClientQueryBuilder())
            ->addFilter(new ActiveClientsFilter())
            ->build()
            ->get();

        if ($clients->isEmpty()) {
            return null;
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

    protected function generate(): int
    {
        $client = $this->getClient();

        if (is_null($client)) {
            $this->error('No active clients found.');

            return static::SUCCESS;
        }

        $days = $this->option('valid-days') ?? text(
            label: 'Validity period in days',
            default: '365',
            required: true,
        );

        if (! is_numeric($days) || (int) $days <= 0) {
            $this->error('Invalid number of days');

            return static::FAILURE;
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

        return static::SUCCESS;
    }

    protected function rotate(): int
    {
        return $this->generate();
    }

    protected function listKeys(): int
    {
        $client = $this->getClient();

        if (is_null($client)) {
            $this->error('No active clients found.');

            return static::SUCCESS;
        }

        $keys = $this->getAllKeysForClientId($client->id);

        if ($keys->isEmpty()) {
            $this->info('No keys found for this client.');

            return static::SUCCESS;
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

        return static::SUCCESS;
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

    protected function revokeKey(): int
    {
        $client = $this->getClient();

        if (is_null($client)) {
            $this->error('No active clients found.');

            return static::SUCCESS;
        }

        $key = $this->getActiveKeyForClientId($client->id);

        if (! $key instanceof Key) {
            $this->error("Key not found for client ID {$client->id}.");

            return static::SUCCESS;
        }

        VaultKey::revoke($key->id);

        $this->info("Key with ID {$key->id} revoked successfully.");

        return static::SUCCESS;
    }

    protected function cleanupKeys(): int
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

        return static::SUCCESS;
    }
}
