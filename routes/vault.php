<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Route;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Http\Controllers\ClientController;
use JuniorFontenele\LaravelVaultServer\Http\Controllers\HashController;
use JuniorFontenele\LaravelVaultServer\Http\Controllers\KmsController;

Route::group([
    'prefix' => config('vault.url_prefix', 'vault'),
    'as' => config('vault.route_prefix', 'vault') . '.',
    'middleware' => ['api'],
], function () {
    Route::post('/client/{clientId}/provision', [ClientController::class, 'provision'])
        ->name('client.provision');

    Route::get('/hash/{userId}', [HashController::class, 'show'])
        ->middleware(['vault.jwt:' . Scope::HASHES_READ->value])
        ->name('hash.get');

    Route::post('/hash/{userId}', [HashController::class, 'store'])
        ->middleware(['vault.jwt:' . Scope::HASHES_CREATE->value])
        ->name('hash.store');

    Route::delete('/hash/{userId}', [HashController::class, 'destroy'])
        ->middleware(['vault.jwt:' . Scope::HASHES_DELETE->value])
        ->name('hash.destroy');

    Route::post('/kms/{kid}/rotate', [KmsController::class, 'rotate'])
        ->middleware(['vault.jwt:' . Scope::KEYS_ROTATE->value])
        ->name('kms.rotate');

    Route::get('/kms/{kid}', [KmsController::class, 'show'])
        ->name('kms.get');
});
