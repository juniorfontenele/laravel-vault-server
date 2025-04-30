<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Interfaces\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades\VaultHash;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades\VaultJWT;
use JuniorFontenele\LaravelVaultServer\Interfaces\Http\Resources\HashResource;

class HashController
{
    public function show(Request $request, string $userId)
    {
        $hashModel = VaultHash::getByUserId($userId);

        if (! $hashModel) {
            return response()->json(['error' => 'Hash not found'], 404);
        }

        return $hashModel->toResource(HashResource::class);
    }

    public function store(Request $request, string $userId)
    {
        $validator = Validator::make($request->all(), [
            'hash' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $decodedToken = VaultJWT::decode($request->bearerToken());

        $hashModel = VaultHash::store(
            $decodedToken->client_id,
            $userId,
            $request->input('hash')
        );

        return $hashModel->toResource(HashResource::class);
    }

    public function destroy(string $userId)
    {
        if (VaultHash::delete($userId)) {
            return response()->noContent();
        }

        return response()->json(['error' => 'Failed to delete hash for user ' . $userId], 422);
    }
}
