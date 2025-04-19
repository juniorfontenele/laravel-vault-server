<?php

declare(strict_types = 1);

return [
    'url_prefix' => 'vault', // e.g. https://example.com/vault
    'route_prefix' => 'vault', // e.g. vault.client.provision

    'migrations' => [
        'table_prefix' => 'vault_',
    ],
];
