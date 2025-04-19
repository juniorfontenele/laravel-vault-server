<?php

declare(strict_types = 1);

return [
    // Client Configuration
    'url' => env('VAULT_URL', config('app.url') . '/vault'),
    'issuer' => env('VAULT_ISSUER', config('app.url')),
    'client_id' => env('VAULT_CLIENT_ID', null),

    'token_expiration_time' => 60, // 1 minute
    'cache_ttl' => 60 * 60, // 1 hour

    // Server Configuration
    'server_enabled' => true,
    'url_prefix' => 'vault', // e.g. https://example.com/vault
    'route_prefix' => 'vault', // e.g. vault.client.provision

    'migrations' => [
        'table_prefix' => 'vault_',
    ],
];
