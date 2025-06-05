<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Filters\Client;

use Illuminate\Contracts\Database\Query\Builder;
use JuniorFontenele\LaravelVaultServer\Contracts\QueryFilterInterface;

class InactiveClientsFilter implements QueryFilterInterface
{
    public function apply(Builder $query): Builder
    {
        return $query->where('is_active', '=', false);
    }
}
