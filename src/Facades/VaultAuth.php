<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Facades;

use Illuminate\Support\Facades\Facade;
use JuniorFontenele\LaravelVaultServer\Services\JwtAuthService;

class VaultAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return JwtAuthService::class;
    }
}
