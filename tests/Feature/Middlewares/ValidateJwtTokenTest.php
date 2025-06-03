<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Models\Pepper;

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

beforeEach(function () {
    Pepper::create([
        'version' => 1,
        'value' => 'pepper',
        'is_revoked' => false,
    ]);
});

it('rejects requests without token', function () {
    $response = $this->postJson(route('vault.password.store', 'userX'), [
        'password' => 'secret',
    ]);

    $response->assertUnauthorized();
});

// success scenario covered in other integration tests
