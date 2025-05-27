<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Console\Commands;

use Illuminate\Console\Command;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\ClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\KeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\FindAllClientsUseCase;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;

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

    protected function getClient(): ClientResponseDTO
    {
        $clients = collect(app(FindAllClientsUseCase::class)->execute());

        if ($clients->isEmpty()) {
            $this->error('No active clients found.');

            exit(static::FAILURE);
        }

        $clientUuid = $this->option('client') ?? search(
            label: 'Search for a client',
            options: fn (string $value) => $clients
                ->filter(function (ClientResponseDTO $client) use ($value) {
                    return str_contains($client->name, $value)
                    || str_contains($client->clientId, $value);
                })
                ->mapWithKeys(
                    fn (ClientResponseDTO $client) => [$client->clientId => "{$client->name} ({$client->clientId})"],
                )
                ->toArray(),
            required: true,
        );

        $client = $clients->where('clientId', $clientUuid)->first();

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

        $createKeyResponseDTO = VaultKey::createKeyForClient($client->clientId, 2048, (int) $days);

        $this->info("Key pair generated successfully.");
        $this->line("KID: {$createKeyResponseDTO->keyId}");
        $this->line("Public Key:");
        $this->line($createKeyResponseDTO->publicKey);
        $this->line("Private Key:");
        $this->line($createKeyResponseDTO->privateKey);
        $this->warn("Keep the private key safe!");

        exit(static::SUCCESS);
    }

    protected function rotate()
    {
        $client = $this->getClient();

        $key = VaultKey::findByClientId($client->clientId);

        if (! $key) {
            $this->error('No valid key found for this client.');

            exit(static::FAILURE);
        }

        $days = $this->option('valid-days') ?? text(
            label: 'Validity period in days',
            default: '365',
            required: true,
        );

        if (! is_numeric($days) || (int) $days <= 0) {
            return $this->error('Invalid number of days');
        }

        $createKeyResponseDto = VaultKey::rotate(
            keyId: $key->keyId,
            keySize: 2048,
            expiresIn: (int) $days,
        );

        $this->info("Key pair rotated successfully.");
        $this->line("KID: {$createKeyResponseDto->keyId}");
        $this->line("Public Key:");
        $this->line($createKeyResponseDto->publicKey);
        $this->line("Private Key:");
        $this->line($createKeyResponseDto->privateKey);
        $this->warn("Keep the private key safe!");

        exit(static::SUCCESS);
    }

    protected function listKeys()
    {
        $client = $this->getClient();

        $keys = collect(VaultKey::findAllKeysByClientId($client->clientId));

        if ($keys->isEmpty()) {
            $this->info('No keys found for this client.');

            return;
        }

        $this->table(['ID', 'Public Key', 'Revoked?', 'Valid From', 'Valid Until'], $keys->map(function (KeyResponseDTO $keyResponseDTO) {
            return [
                'id' => $keyResponseDTO->keyId,
                'public_key' => $keyResponseDTO->publicKey,
                'is_revoked' => $keyResponseDTO->isRevoked ? '✅' : '❌',
                'valid_from' => $keyResponseDTO->validFrom,
                'valid_until' => $keyResponseDTO->validUntil,
            ];
        })->toArray());

        exit(static::SUCCESS);
    }

    protected function getKeyForClientId(string $clientId): KeyResponseDTO
    {
        $keys = collect(VaultKey::findAllNonRevokedKeysByClientId($clientId));

        if ($keys->isEmpty()) {
            $this->info('No valid keys found for this client.');

            exit(static::FAILURE);
        }

        $keyId = $this->option('key-id') ?? search(
            label: 'Select a key',
            options: fn () => $keys->mapWithKeys(
                fn (KeyResponseDTO $key) => [$key->keyId => "{$key->keyId} (Expires: {$key->validUntil->format('Y-m-d')})"],
            )->toArray(),
            required: true,
        );

        $key = $keys->where('keyId', $keyId)->first();

        if (! $key) {
            $this->error('Key not found');

            exit(static::FAILURE);
        }

        return $key;
    }

    protected function deleteKey()
    {
        $client = $this->getClient();

        $key = $this->getKeyForClientId($client->clientId);

        VaultKey::deleteKey($key->keyId);

        $this->info("Key with ID {$key->keyId} deleted successfully.");

        exit(static::SUCCESS);
    }

    protected function revokeKey()
    {
        $client = $this->getClient();

        $key = $this->getKeyForClientId($client->clientId);

        VaultKey::revokeKey($key->keyId);

        $this->info("Key with ID {$key->keyId} revoked successfully.");

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
