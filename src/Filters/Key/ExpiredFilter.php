<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Filters\Key;

use Illuminate\Contracts\Database\Query\Builder;
use JuniorFontenele\LaravelVaultServer\Contracts\QueryFilterInterface;

class ExpiredFilter implements QueryFilterInterface
{
    public function apply(Builder $query): Builder
    {
        return $query->where('valid_until', '<=', now());
    }
}
