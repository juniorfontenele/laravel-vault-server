<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Artifacts;

use JuniorFontenele\LaravelVaultServer\Models\ClientModel;

class NewClient
{
    public function __construct(
        public ClientModel $client,
        public string $plaintextProvisionToken,
    ) {
        //
    }
}
