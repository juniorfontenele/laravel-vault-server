<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Filters\Key;

use Illuminate\Contracts\Database\Query\Builder;
use JuniorFontenele\LaravelVaultServer\Contracts\QueryFilterInterface;

class ByClientIdFilter implements QueryFilterInterface
{
    public function __construct(
        private string $clientId,
    ) {
        //
    }

    public function apply(Builder $query): Builder
    {
        return $query->where('client_id', '=', $this->clientId);
    }
}
