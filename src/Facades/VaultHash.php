<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Facades;

use Illuminate\Support\Facades\Facade;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Services\HashService;

class VaultHash extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return HashService::class;
    }
}
