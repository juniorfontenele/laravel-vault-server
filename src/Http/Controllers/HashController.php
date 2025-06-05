<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use JuniorFontenele\LaravelVaultServer\Exceptions\Hash\HashStoreException;
use JuniorFontenele\LaravelVaultServer\Exceptions\Hash\RehashNeededException;
use JuniorFontenele\LaravelVaultServer\Facades\VaultHash;

class HashController extends Controller
{
    public function verify(Request $request, string $userId)
    {
        $ip = $request->ip();
        $key = 'hash_verify_attempts:' . $userId . ':' . $ip;
        $maxAttempts = 5;
        $decaySeconds = 120;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many attempts. Please try again later.',
            ], 429);
        }

        $validated = validator($request->all(), [
            'password' => ['required', 'string', 'max:255'],
        ])->validate();

        try {
            $hashVerified = VaultHash::verify(
                $userId,
                $validated['password'],
            );

            if (! $hashVerified) {
                RateLimiter::hit($key, $decaySeconds);

                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return response()->json(['message' => 'Authorized', 'needs_rehash' => false], 200);
        } catch (RehashNeededException $e) {
            return response()->json(['message' => 'Authorized', 'needs_rehash' => true], 200);
        }
    }

    public function store(Request $request, string $userId)
    {
        $validated = validator($request->all(), [
            'password' => [
                'required',
                'string',
                'max:255',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ])->validate();

        try {
            VaultHash::store(
                $userId,
                $validated['password'],
            );

            return response()->json(['message' => 'Password stored successfully'], 201);
        } catch (HashStoreException $e) {
            return response()->json(['message' => 'Failed to store password'], 422);
        }
    }

    public function destroy(string $userId)
    {
        try {
            VaultHash::delete($userId);

            return response()->json(['message' => 'Password deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete password for user'], 422);
        }
    }
}
