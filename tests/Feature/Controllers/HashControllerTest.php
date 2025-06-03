<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Models\Hash;
use JuniorFontenele\LaravelVaultServer\Models\Pepper;

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

beforeEach(function () {
    Hash::query()->delete();
    Pepper::query()->delete();
    Pepper::create([
        'version' => 1,
        'value' => 'pepper',
        'is_revoked' => false,
    ]);
});

it('fails validation when password missing', function () {
    $response = $this->postJson(route('vault.password.store', 'user1'), []);

    $response->assertUnauthorized();
});
