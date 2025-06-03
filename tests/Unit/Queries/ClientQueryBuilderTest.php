<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Queries\Client\ClientQueryBuilder;
use JuniorFontenele\LaravelVaultServer\Queries\Client\Filters\InactiveClientsFilter;

beforeEach(function () {
    $this->loadVaultMigrations();
});

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

it('filters inactive clients', function () {
    $active = Client::factory()->create();
    $inactive = Client::factory()->create(['is_active' => false]);

    $results = app(ClientQueryBuilder::class)
        ->addFilter(app(InactiveClientsFilter::class))
        ->build()
        ->get();

    expect($results->pluck('id')->all())
        ->toContain($inactive->id)
        ->not()->toContain($active->id);
});
