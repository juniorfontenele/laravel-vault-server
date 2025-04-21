<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Console\Commands;

use Illuminate\Console\Command;
use JuniorFontenele\LaravelVaultServer\Enums\Permission;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Models\Client;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class VaultClientManagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vault:client
        {action? : Action to perform (create|delete|list|cleanup|provision)}
        {--client= : Client UUID (for delete)}
        {--name= : Client name (for create)}
        {--description= : Client description (for create)}
        {--scopes= : Allowed scopes (comma-separated)}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vault Client Management';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $action = $this->argument('action') ?? select(
            'Select an action',
            [
                'create' => 'Create a new client',
                'delete' => 'Delete an existing client',
                'provision' => 'Provision an existing client',
                'list' => 'List all clients',
                'cleanup' => 'Cleanup inactive clients',
            ],
            required: true,
        );

        match ($action) {
            'create' => $this->createClient(),
            'delete' => $this->deleteClient(),
            'provision' => $this->provisionClient(),
            'list' => $this->listClients(),
            'cleanup' => $this->cleanupClients(),
            default => $this->error("Action '{$action}' not supported."),
        };
    }

    protected function createClient(): void
    {
        $name = $this->option('name');
        $description = $this->option('description');

        if (! $name) {
            $name = text('What is the client\'s name?', 'https://acme.company.com', required: true);
        }

        if (! $description) {
            $description = text('What is the client\'s description?', 'ACME Server Description');
        }

        $scopes = $this->option('scopes') ?? multiselect(
            label: 'Allowed scopes',
            options: Permission::toArray(),
            default: [
                Permission::KEYS_READ->value,
                Permission::KEYS_ROTATE->value,
            ],
            required: true,
        );

        $scopes = is_array($scopes) ? array_map('trim', $scopes) : explode(',', $scopes);

        if (empty($scopes[0])) {
            $this->error('--scopes is required');

            exit(static::FAILURE);
        }

        $client = VaultClientManager::createClient(
            name: $name,
            allowedScopes: $scopes,
            description: $description,
        );

        if (! $client) {
            $this->error('Failed to create client.');

            exit(static::FAILURE);
        }

        $provisionToken = VaultClientManager::generateProvisionToken($client);

        $this->info("Client '{$name}' created successfully.");
        $this->info("Client ID: {$client->id}");
        $this->info("Provision Token: {$provisionToken}");
    }

    protected function listClients(): void
    {
        $clients = Client::query()->active()->get(['id', 'name', 'description']);

        if ($clients->isEmpty()) {
            $this->info('No clients found.');

            return;
        }

        $this->table(['ID', 'Name', 'Description'], $clients->toArray());
    }

    protected function deleteClient(): void
    {
        if (Client::query()->active()->count() === 0) {
            $this->info('No clients found to delete.');

            return;
        }

        $clientUuid = $this->option('client') ?? search(
            label: 'Search for a client to delete',
            options: fn (string $value) => Client::query()
                ->active()
                ->where('id', 'like', "%{$value}%")
                ->orWhere('name', 'like', "%{$value}%")
                ->get()
                ->mapWithKeys(fn ($client) => [$client->id => "{$client->name} - {$client->id}"])
                ->toArray(),
            required: true,
        );

        $client = Client::query()->active()->where('id', $clientUuid)->first();

        if (! $client) {
            $this->error("Client with UUID {$clientUuid} not found.");

            return;
        }

        $client->delete();

        $this->info("Client with UUID {$clientUuid} deleted successfully.");
    }

    protected function cleanupClients(): void
    {
        $deleted = VaultClientManager::cleanupInactiveClients();

        if ($deleted === 0) {
            $this->info('No inactive clients found.');

            return;
        }

        $this->info("Deleted {$deleted} inactive clients.");
    }

    protected function provisionClient(): void
    {
        if (Client::query()->active()->count() === 0) {
            $this->info('No clients found to provision.');

            return;
        }

        $clientUuid = $this->option('client') ?? search(
            label: 'Search for a client to provision',
            options: fn (string $value) => Client::query()
                ->active()
                ->where('id', 'like', "%{$value}%")
                ->orWhere('name', 'like', "%{$value}%")
                ->get()
                ->mapWithKeys(fn ($client) => [$client->id => "{$client->name} - {$client->id}"])
                ->toArray(),
            required: true,
        );

        $client = Client::query()
            ->active()
            ->where('id', $clientUuid)
            ->first();

        if (! $client) {
            $this->error("Client with UUID {$clientUuid} not found.");

            return;
        }

        $provisionToken = VaultClientManager::generateProvisionToken($client);

        $this->info("Client ID: {$client->id}");
        $this->info("Provision Token: {$provisionToken}");
    }
}
