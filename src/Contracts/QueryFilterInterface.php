<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Contracts;

use Illuminate\Contracts\Database\Query\Builder;

interface QueryFilterInterface
{
    public function apply(Builder $query): Builder;
}
