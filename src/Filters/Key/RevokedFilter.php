<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Filters\Key;

use Illuminate\Contracts\Database\Query\Builder;
use JuniorFontenele\LaravelVaultServer\Contracts\QueryFilterInterface;

class RevokedFilter implements QueryFilterInterface
{
    public function apply(Builder $query): Builder
    {
        return $query->where('is_revoked', '=', true);
    }
}
