<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JuniorFontenele\LaravelSecureJwt\Exceptions\JwtException;
use JuniorFontenele\LaravelSecureJwt\Exceptions\JwtValidationException;
use JuniorFontenele\LaravelSecureJwt\Facades\SecureJwt;
use JuniorFontenele\LaravelSecureJwt\JwtKey;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotAuthorizedException;
use JuniorFontenele\LaravelVaultServer\Exceptions\Jwt\InvalidJwtHeader;
use JuniorFontenele\LaravelVaultServer\Exceptions\Jwt\KidNotFoundInJwt;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;

class ValidateJwtToken
{
    public function handle(Request $request, Closure $next, $requestedScope)
    {
        try {
            $token = $request->bearerToken();

            if (empty($token)) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

            $kid = $this->getKidFromBase64Token($token);

            $key = VaultKey::get($kid);

            $decodedToken = SecureJwt::decode($token, new JwtKey(
                $key->id,
                $key->public_key,
                $key->algorithm
            ));

            $authorizedScopes = $decodedToken->claim('scope');

            if (is_null($authorizedScopes)) {
                throw new JwtValidationException('No scopes found in the token');
            }

            $authorizedScopes = explode(' ', $authorizedScopes);
            $authorizedScopes = array_map('trim', $authorizedScopes);
            $authorizedScopes = array_map('strtolower', $authorizedScopes);

            $requestedScope = strtolower($requestedScope);

            if (! in_array($requestedScope, $authorizedScopes, true)) {
                throw new ClientNotAuthorizedException($requestedScope);
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

    private function getKidFromBase64Token(string $token): string
    {
        [$header, $payload, $signature] = explode('.', $token);
        $decodedHeader = base64_decode($header);

        if ($decodedHeader === false) {
            throw new InvalidJwtHeader();
        }

        if (! isset($decodedHeader['kid'])) {
            throw new KidNotFoundInJwt();
        }

        return $decodedHeader['kid'];
    }
}
