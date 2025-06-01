<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Artifacts;

use JuniorFontenele\LaravelVaultServer\Models\Client;

class NewClient
{
    public function __construct(
        public Client $client,
        public string $plaintextProvisionToken,
    ) {
        //
    }
}
