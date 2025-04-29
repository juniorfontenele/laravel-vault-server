<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\ClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\DeleteClient;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\DeleteInactiveClients;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\FindAllActiveClients;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\FindAllClients;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\FindAllInactiveClients;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\ReprovisionClient;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;

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

        $client = VaultClientManager::createClient(
            name: $name,
            allowedScopes: $scopes,
            description: $description,
        );

        $this->info("Client '{$name}' created successfully.");
        $this->info("Client ID: {$client->id}");
        $this->info("Provision Token: {$client->provisionToken}");
    }

    protected function listClients(): void
    {
        $clients = $this->getAllActiveClients();

        if ($clients->isEmpty()) {
            $this->info('No clients found.');

            return;
        }

        $rows = $clients->map(function (ClientResponseDTO $client) {
            return [
                'ID' => $client->id,
                'Name' => $client->name,
                'Description' => $client->description,
                'Scopes' => implode(', ', $client->allowedScopes),
            ];
        })->toArray();

        $this->table(['ID', 'Name', 'Description', 'Scopes'], $rows);
    }

    protected function deleteClient(): void
    {
        $deleteClient = app(DeleteClient::class);
        $clients = $this->getAllClients();

        if ($clients->count() === 0) {
            $this->info('No clients found to delete.');

            return;
        }

        $clientUuid = $this->option('client') ?? search(
            label: 'Search for a client to delete',
            options: fn (string $value) => $clients
                ->filter(function (ClientResponseDTO $client) use ($value) {
                    return str_contains($client->id, $value) || str_contains($client->name, $value);
                })
                ->mapWithKeys(fn ($client) => [$client->id => "{$client->name} - {$client->id}"])
                ->toArray(),
            required: true,
        );

        $deleteClient->execute($clientUuid);

        $this->info("Client with UUID {$clientUuid} deleted successfully.");
    }

    protected function cleanupClients(): void
    {
        $deletedClients = collect(app(DeleteInactiveClients::class)->execute());

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
                ->filter(function (ClientResponseDTO $client) use ($value) {
                    return str_contains($client->id, $value) || str_contains($client->name, $value);
                })
                ->mapWithKeys(fn ($client) => [$client->id => "{$client->name} - {$client->id}"])
                ->toArray(),
            required: true,
        );

        $reprovisionClient = app(ReprovisionClient::class);

        $client = $reprovisionClient->execute($clientUuid);

        $this->info("Client ID: {$client->id}");
        $this->info("Provision Token: {$client->provisionToken}");
    }

    /**
     * Get all active clients.
     *
     * @return Collection<ClientResponseDTO>
     */
    protected function getAllActiveClients(): Collection
    {
        return collect(app(FindAllActiveClients::class)->execute());
    }

    /**
     * Get all inactive clients.
     *
     * @return Collection<ClientResponseDTO>
     */
    protected function getAllInactiveClients(): Collection
    {
        return collect(app(FindAllInactiveClients::class)->execute());
    }

    /**
     * Get all clients.
     *
     * @return Collection<ClientResponseDTO>
     */
    protected function getAllClients(): Collection
    {
        return collect(app(FindAllClients::class)->execute());
    }
}
