<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Facades;

use Illuminate\Support\Facades\Facade;
use JuniorFontenele\LaravelVaultServer\Services\ClientManagerService;

class VaultClientManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ClientManagerService::class;
    }
}
