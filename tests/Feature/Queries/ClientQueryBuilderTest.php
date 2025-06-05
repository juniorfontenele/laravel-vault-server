<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Filters\Client\InactiveClientsFilter;
use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Queries\ClientQueryBuilder;

beforeEach(function () {
    $this->loadVaultMigrations();
});

describe('ClientQueryBuilder', function () {
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
});
