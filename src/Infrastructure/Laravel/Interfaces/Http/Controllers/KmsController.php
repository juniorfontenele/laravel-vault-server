<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Interfaces\Http\Controllers;

use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;

class KmsController
{
    public function show(string $kid)
    {
        $keyResponseDTO = VaultKey::findByKid($kid);

        if (! $keyResponseDTO) {
            return response()->json([
                'message' => 'No key found.',
            ], 404);
        }

        return response()->json($keyResponseDTO->toArray());
    }

    public function rotate(string $kid)
    {
        $keyResponseDTO = VaultKey::findByKid($kid);

        if (! $keyResponseDTO) {
            return response()->json([
                'message' => 'No key found.',
            ], 404);
        }

        $createKeyResponseDTO = VaultKey::rotate($keyResponseDTO->keyId);

        return response()->json($createKeyResponseDTO->toArray(), 201);
    }
}
