<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Enums\AuditAction;
use JuniorFontenele\LaravelVaultServer\Models\Audit;
use JuniorFontenele\LaravelVaultServer\Models\Client;

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

beforeEach(function () {
    Audit::query()->delete();
    Client::query()->delete();
});

it('creates audit records on model events', function () {
    $client = Client::factory()->create();

    expect(Audit::count())->toBe(1)
        ->and(Audit::first()->action)->toBe(AuditAction::CREATE);

    $retrieved = Client::find($client->id);
    $retrieved->update(['description' => 'changed']);
    $retrieved->delete();

    expect(Audit::count())->toBe(4);
});
