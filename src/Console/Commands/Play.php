<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Console\Commands;

use Illuminate\Console\Command;
use JuniorFontenele\LaravelVaultServer\Actions\Client\CreateClientAction;
use JuniorFontenele\LaravelVaultServer\Actions\Client\ListClientsAction;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Filters\ClientFilter;

class Play extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(CreateClientAction $createClient, ListClientsAction $listClients): void
    {
        $client = $createClient->execute(
            name: 'Test Client',
            allowedScopes: [Scope::KEYS_READ, Scope::KEYS_ROTATE],
            description: 'This is a test client.',
        );
        dump($client);

        $filter = new ClientFilter();

        $clients = $listClients->execute($filter);

        dump($clients->toArray());
    }
}
