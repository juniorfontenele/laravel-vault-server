<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Http\Controllers;

use Illuminate\Routing\Controller;
use JuniorFontenele\LaravelVaultServer\Data\Key\KeyData;
use JuniorFontenele\LaravelVaultServer\Data\Key\NewKeyData;
use JuniorFontenele\LaravelVaultServer\Facades\VaultAuth;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;

class KmsController extends Controller
{
    public function show(string $kid)
    {
        $key = VaultKey::get($kid);

        if (! $key) {
            return response()->json([
                'message' => 'No key found.',
            ], 404);
        }

        $response = new KeyData(
            key_id: $key->id,
            version: $key->version,
            public_key: $key->public_key,
            client_id: $key->client_id,
            valid_from: $key->valid_from->toIso8601String(),
            valid_until: $key->valid_until->toIso8601String(),
        );

        return response()->json($response->toArray());
    }

    public function rotate()
    {
        $key = VaultAuth::key();

        if (! $key) {
            abort(401);
        }

        $newKey = VaultKey::rotate($key->id);

        $response = new NewKeyData(
            key_id: $newKey->key->id,
            version: $newKey->key->version,
            public_key: $newKey->key->public_key,
            private_key: $newKey->private_key,
            client_id: $newKey->key->client_id,
            valid_from: $newKey->key->valid_from->toIso8601String(),
            valid_until: $newKey->key->valid_until->toIso8601String(),
        );

        return response()->json($response->toArray(), 201);
    }
}
