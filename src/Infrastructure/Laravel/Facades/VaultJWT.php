<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Services\JwtService;

class VaultJWT extends Facade
{
    protected static function getFacadeAccessor()
    {
        return JwtService::class;
    }
}
