<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Actions\Client;

class GenerateProvisionTokenAction
{
    public function execute(): string
    {
        return bin2hex(random_bytes(32));
    }
}
