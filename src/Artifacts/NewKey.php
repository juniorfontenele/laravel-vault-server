<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Artifacts;

use JuniorFontenele\LaravelVaultServer\Models\KeyModel;

class NewKey
{
    public function __construct(
        public KeyModel $key,
        public string $private_key,
    ) {
        //
    }
}
