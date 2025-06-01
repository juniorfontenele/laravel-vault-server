<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Models\ClientModel;
use JuniorFontenele\LaravelVaultServer\Queries\Client\ClientQueryBuilder;
use JuniorFontenele\LaravelVaultServer\Queries\Client\Filters\ActiveClientsFilter;
use JuniorFontenele\LaravelVaultServer\Queries\Client\Filters\InactiveClientsFilter;

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
            options: Scope::toArray(),
            default: [
                Scope::KEYS_READ->value,
                Scope::KEYS_ROTATE->value,
            ],
            required: true,
        );

        $scopes = is_array($scopes) ? array_map('trim', $scopes) : explode(',', $scopes);

        if (empty($scopes[0])) {
            $this->error('--scopes is required');

            exit(static::FAILURE);
        }

        $newClient = VaultClientManager::createClient(
            name: $name,
            allowedScopes: $scopes,
            description: $description,
        );

        $this->info("Client '{$name}' created successfully.");
        $this->info("Client ID: {$newClient->client->id}");
        $this->info("Provision Token: {$newClient->plaintextProvisionToken}");
    }

    protected function listClients(): void
    {
        $clients = $this->getAllActiveClients();

        if ($clients->isEmpty()) {
            $this->info('No clients found.');

            return;
        }

        $rows = $clients->map(function (ClientModel $client): array {
            return [
                'ID' => $client->id,
                'Name' => $client->name,
                'Provisioned' => $client->provisioned_at ? '✅' : '❌',
                'Scopes' => implode(', ', $client->allowed_scopes),
            ];
        })->toArray();

        $this->table(['ID', 'Name', 'Provisioned', 'Scopes'], $rows);
    }

    protected function deleteClient(): void
    {
        $clients = $this->getAllClients();

        if ($clients->count() === 0) {
            $this->info('No clients found to delete.');

            return;
        }

        $clientUuid = $this->option('client') ?? search(
            label: 'Search for a client to delete',
            options: fn (string $value) => $clients
                ->filter(function (ClientModel $client) use ($value): bool {
                    return str_contains($client->id, $value) || str_contains($client->name, $value);
                })
                ->mapWithKeys(fn (ClientModel $client): array => [$client->id => "{$client->name} - {$client->id}"])
                ->toArray(),
            required: true,
        );

        VaultClientManager::deleteClient($clientUuid);

        $this->info("Client with UUID {$clientUuid} deleted successfully.");
    }

    protected function cleanupClients(): void
    {
        $deletedClients = VaultClientManager::cleanupInactiveClients();

        if ($deletedClients->count() === 0) {
            $this->info('No inactive clients found.');

            return;
        }

        $this->info("Deleted {$deletedClients->count()} inactive clients.");
    }

    protected function provisionClient(): void
    {
        $clients = $this->getAllActiveClients();

        if ($clients->isEmpty()) {
            $this->info('No clients found to provision.');

            return;
        }

        $clientUuid = $this->option('client') ?? search(
            label: 'Search for a client to provision',
            options: fn (string $value) => $clients
                ->filter(function (ClientModel $client) use ($value): bool {
                    return str_contains($client->id, $value) || str_contains($client->name, $value);
                })
                ->mapWithKeys(fn (ClientModel $client): array => [$client->id => "{$client->name} - {$client->id}"])
                ->toArray(),
            required: true,
        );

        $newClient = VaultClientManager::reprovisionClient($clientUuid);

        $this->info("Client ID: {$newClient->client->id}");
        $this->info("Provision Token: {$newClient->plaintextProvisionToken}");
    }

    /**
     * Get all active clients.
     *
     * @return Collection<ClientModel>
     */
    protected function getAllActiveClients(): Collection
    {
        return (new ClientQueryBuilder())
            ->addFilter(new ActiveClientsFilter())
            ->build()
            ->get();
    }

    /**
     * Get all inactive clients.
     *
     * @return Collection<ClientModel>
     */
    protected function getAllInactiveClients(): Collection
    {
        return (new ClientQueryBuilder())
            ->addFilter(new InactiveClientsFilter())
            ->build()
            ->get();
    }

    /**
     * Get all clients.
     *
     * @return Collection<ClientModel>
     */
    protected function getAllClients(): Collection
    {
        return (new ClientQueryBuilder())
            ->build()
            ->get();
    }
}
