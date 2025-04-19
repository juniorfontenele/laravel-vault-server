<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Facades;

use Illuminate\Support\Facades\Facade;
use JuniorFontenele\LaravelVaultServer\Services\VaultClientService;

class VaultClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return VaultClientService::class;
    }
}
