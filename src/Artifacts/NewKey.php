<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Artifacts;

use JuniorFontenele\LaravelVaultServer\Models\Key;

class NewKey
{
    public function __construct(
        public Key $key,
        public string $private_key,
    ) {
        //
    }
}
