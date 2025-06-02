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

    Route::post('/password/{userId}/verify', [HashController::class, 'verify'])
        ->middleware(['vault.jwt:' . Scope::PASSWORDS_VERIFY->value])
        ->name('password.verify');

    Route::post('/password/{userId}', [HashController::class, 'store'])
        ->middleware(['vault.jwt:' . Scope::PASSWORDS_CREATE->value])
        ->name('password.store');

    Route::delete('/password/{userId}', [HashController::class, 'destroy'])
        ->middleware(['vault.jwt:' . Scope::PASSWORDS_DELETE->value])
        ->name('password.destroy');

    Route::post('/kms/rotate', [KmsController::class, 'rotate'])
        ->middleware(['vault.jwt:' . Scope::KEYS_ROTATE->value])
        ->name('kms.rotate');

    Route::get('/kms/{kid}', [KmsController::class, 'show'])
        ->name('kms.get');
});
