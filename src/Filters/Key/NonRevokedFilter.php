<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Filters\Key;

use Illuminate\Contracts\Database\Query\Builder;
use JuniorFontenele\LaravelVaultServer\Contracts\QueryFilterInterface;

class NonRevokedFilter implements QueryFilterInterface
{
    public function apply(Builder $query): Builder
    {
        return $query->where('is_revoked', '=', false);
    }
}
