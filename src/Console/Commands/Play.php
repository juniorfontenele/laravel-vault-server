<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Console\Commands;

use Illuminate\Console\Command;
use JuniorFontenele\LaravelVaultServer\Actions\Client\CreateClientAction;
use JuniorFontenele\LaravelVaultServer\Data\Client\CreateClientData;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;

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
    public function handle()
    {
        $clientData = CreateClientData::fromArray([
            'name' => 'Test Client',
            'allowed_scopes' => [Scope::KEYS_READ, Scope::HASHES_CREATE],
            'description' => 'This is a test client.',
        ]);

        $client = app(CreateClientAction::class)->execute($clientData);

        dump($client->toArray());
    }
}
