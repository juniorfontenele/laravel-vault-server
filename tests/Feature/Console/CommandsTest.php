<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Artisan;

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

it('runs client list command', function () {
    $exitCode = Artisan::call('vault-server:client', [
        'action' => 'list',
        '--no-interaction' => true,
    ]);

    expect($exitCode)->toBe(0);
});

it('runs key cleanup command', function () {
    $exitCode = Artisan::call('vault-server:key', [
        'action' => 'cleanup',
        '--no-interaction' => true,
    ]);

    expect($exitCode)->toBe(0);
});
