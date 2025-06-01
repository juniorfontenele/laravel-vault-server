<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Queries\Hash\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use JuniorFontenele\LaravelVaultServer\Contracts\QueryFilterInterface;

class HashForUserId implements QueryFilterInterface
{
    public function __construct(
        protected string $userId
    ) {
        //
    }

    public function apply(Builder $query): Builder
    {
        return $query->where('user_id', $this->userId);
    }
}
