<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Services\ClientManagerService;

class VaultClientManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ClientManagerService::class;
    }
}
