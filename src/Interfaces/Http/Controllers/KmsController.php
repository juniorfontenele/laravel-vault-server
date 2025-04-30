<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Interfaces\Http\Controllers;

use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Interfaces\Http\Resources\KeyResource;

class KmsController
{
    public function show(string $kid)
    {
        $key = VaultKey::findByKid($kid);

        if (! $key) {
            return response()->json([
                'message' => 'No key found.',
            ], 404);
        }

        return $key->toResource(KeyResource::class);
    }

    public function rotate(string $kid)
    {
        $key = VaultKey::findByKid($kid);

        if (! $key) {
            return response()->json([
                'message' => 'No key found.',
            ], 404);
        }

        [$newKey, $privateKey] = VaultKey::rotate($key);

        $newKey->private_key = $privateKey;

        return $newKey->toResource(KeyResource::class);
    }
}
