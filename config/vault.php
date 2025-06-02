<?php

declare(strict_types = 1);

return [
    'url_prefix' => 'vault', // e.g. https://example.com/vault
    'route_prefix' => 'vault', // e.g. vault.client.provision

    'migrations' => [
        'table_prefix' => 'vault_',
    ],

    'jwt' => [
        'issuer' => env('VAULT_JWT_ISSUER', 'http://localhost'),
        'ttl' => env('VAULT_JWT_TTL', 60 * 5), // 5 minutes
        'nonce_ttl' => env('VAULT_JWT_NONCE_TTL', 60 * 24), // 24 hours
        'blacklist_ttl' => env('VAULT_JWT_BLACKLIST_TTL', 60 * 60 * 30), // 30 days
    ],

    'providers' => [
        'jwt_driver' => JuniorFontenele\LaravelSecureJwt\Drivers\FirebaseJwtDriver::class,
        'jwt_blacklist' => JuniorFontenele\LaravelSecureJwt\Repositories\LaravelCacheBlacklistRepository::class,
        'jwt_nonce' => JuniorFontenele\LaravelSecureJwt\Repositories\LaravelCacheNonceRepository::class,
        'jwt_claim_validator' => JuniorFontenele\LaravelSecureJwt\Validators\JwtClaimValidator::class,
    ],
];
