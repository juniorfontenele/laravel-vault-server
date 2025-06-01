<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Queries\Key\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use JuniorFontenele\LaravelVaultServer\Contracts\QueryFilterInterface;

class ByKeyId implements QueryFilterInterface
{
    public function __construct(
        private string $keyId,
    ) {
        //
    }

    public function apply(Builder $query): Builder
    {
        return $query->where('id', '=', $this->keyId);
    }
}
