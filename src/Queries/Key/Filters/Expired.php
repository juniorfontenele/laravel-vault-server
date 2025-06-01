<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Queries\Key\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use JuniorFontenele\LaravelVaultServer\Contracts\QueryFilterInterface;

class Expired implements QueryFilterInterface
{
    public function apply(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }
}
