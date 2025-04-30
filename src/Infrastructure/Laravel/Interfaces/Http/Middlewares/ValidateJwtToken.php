<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Interfaces\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Exceptions\JwtException;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Exceptions\VaultException;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades\VaultJWT;

class ValidateJwtToken
{
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        try {
            $token = $request->bearerToken();

            if (empty($token)) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

            VaultJWT::validate($token, $scopes);

            return $next($request);
        } catch (JwtException $e) {
            Log::error('Validate JWT failed', [
                'error' => 'Token validation failed',
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Token validation failed',
                'message' => $e->getMessage(),
            ], 403);
        } catch (VaultException $e) {
            Log::error('Validate JWT failed', [
                'error' => 'Vault validation failed',
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Vault validation failed',
                'message' => $e->getMessage(),
            ], 403);
        }
    }
}
