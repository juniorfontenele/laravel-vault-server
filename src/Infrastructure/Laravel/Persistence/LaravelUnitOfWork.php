<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence;

use Illuminate\Support\Facades\DB;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\UnitOfWorkInterface;

class LaravelUnitOfWork implements UnitOfWorkInterface
{
    public function execute(callable $operations)
    {
        return DB::transaction(function () use ($operations) {
            return $operations();
        });
    }
}
