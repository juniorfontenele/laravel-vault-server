<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JuniorFontenele\LaravelSecureJwt\Exceptions\JwtException;
use JuniorFontenele\LaravelSecureJwt\Exceptions\JwtValidationException;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotAuthorizedException;
use JuniorFontenele\LaravelVaultServer\Facades\VaultAuth;

class ValidateJwtToken
{
    public function handle(Request $request, Closure $next, ?string $requestedScope = null)
    {
        try {
            $token = $request->bearerToken();

            if (empty($token)) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

            VaultAuth::attempt($token);

            if (! is_null($requestedScope)) {
                VaultAuth::authorize($requestedScope);
            }

            return $next($request);
        } catch (JwtValidationException $e) {
            Log::error('Token validation failed', [
                'error' => 'Token validation failed',
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Token validation failed',
                'message' => $e->getMessage(),
            ], 422);
        } catch (JwtException $e) {
            Log::error('Invalid token', [
                'error' => 'Invalid token',
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Invalid token',
                'message' => $e->getMessage(),
            ], 422);
        } catch (ClientNotAuthorizedException $e) {
            Log::error('Client not authorized', [
                'error' => 'Client not authorized',
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Client not authorized',
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            Log::error('An error occurred while validating the token', [
                'error' => 'An error occurred while validating the token',
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'An error occurred while validating the token',
            ], 422);
        }
    }
}
