<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JuniorFontenele\LaravelVaultServer\Facades\VaultHash;

class HashController
{
    public function verify(Request $request, string $userId)
    {
        $hash = VaultHash::get($userId);

        if (! $hash) {
            return response()->json(['error' => 'Hash not found'], 404);
        }

        return response()->json($hash->toArray());
    }

    public function store(Request $request, string $userId)
    {
        $validator = Validator::make($request->all(), [
            'hash' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $hashResponseDTO = VaultHash::store(
            $userId,
            $validator->validated()['hash'],
        );

        return response()->json($hashResponseDTO->toArray(), 201);
    }

    public function destroy(string $userId)
    {
        try {
            VaultHash::delete($userId);

            return response()->noContent();
        } catch (\Exception $e) {
            return response()->json(['error' => __('Failed to delete hash for user :userId', ['userId' => $userId])], 422);
        }
    }
}
