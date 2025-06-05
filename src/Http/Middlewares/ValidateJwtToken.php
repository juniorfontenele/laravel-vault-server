<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotAuthorizedException;
use JuniorFontenele\LaravelVaultServer\Facades\VaultAuth;
use Throwable;

class ValidateJwtToken
{
    public function handle(Request $request, Closure $next, ?string $requestedScope = null)
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            VaultAuth::attempt($token);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        if (! is_null($requestedScope)) {
            try {
                VaultAuth::authorize($requestedScope);
            } catch (ClientNotAuthorizedException $e) {
                Log::error('Client not authorized for scope', [
                    'scope' => $requestedScope,
                    'client_id' => VaultAuth::getKey()->client_id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'message' => 'Forbidden: Insufficient scope permissions',
                ], 403);
            }
        }

        return $next($request);
    }
}
