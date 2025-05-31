<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Data;

use JuniorFontenele\LaravelVaultServer\Enums\Scope;

class Teste
{
    /**
     * @param string $name
     * @param Scope[] $scopes
     */
    public function __construct(
        public string $name,
        public array $scopes,
    ) {
        //
    }
}
