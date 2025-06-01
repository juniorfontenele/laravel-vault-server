<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Queries\Client\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use JuniorFontenele\LaravelVaultServer\Contracts\QueryFilterInterface;

class ActiveClientsFilter implements QueryFilterInterface
{
    public function apply($query): Builder
    {
        return $query->where('is_active', true);
    }
}
