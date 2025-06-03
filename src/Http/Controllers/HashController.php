<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JuniorFontenele\LaravelVaultServer\Exceptions\Hash\RehashNeededException;
use JuniorFontenele\LaravelVaultServer\Facades\VaultHash;

class HashController
{
    public function verify(Request $request, string $userId)
    {
        $validated = validator($request->all(), [
            'password' => ['required', 'string', 'max:255'],
        ])->validate();

        try {
            $hashVerified = VaultHash::verify(
                $userId,
                $validated['password'],
            );

            if (! $hashVerified) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return response()->json(['message' => 'Authorized', 'needs_rehash' => false], 200);
        } catch (RehashNeededException $e) {
            return response()->json(['message' => 'Authorized', 'needs_rehash' => true], 200);
        }
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
