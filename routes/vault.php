<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Route;
use JuniorFontenele\LaravelVaultServer\Http\Controllers\ClientController;
use JuniorFontenele\LaravelVaultServer\Http\Controllers\KmsController;

Route::group([
    'prefix' => config('vault.url_prefix', 'vault'),
    'as' => config('vault.route_prefix', 'vault') . '.',
    'middleware' => ['api'],
], function () {
    Route::post('/client/{clientId}/provision', [ClientController::class, 'provision'])
        ->name('client.provision');

    Route::post('/kms/{kid}/rotate', [KmsController::class, 'rotate'])
        ->middleware(['vault.jwt:keys:rotate'])
        ->name('kms.rotate');

    Route::get('/kms/{kid}', [KmsController::class, 'show'])
        ->name('kms.get');
});
