<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Filters;

use Illuminate\Contracts\Database\Query\Builder;

class ClientFilter
{
    public function __construct(
        protected ?string $name = null,
        protected ?array $allowedScopes = null,
        protected ?bool $active = null,
    ) {
        //
    }

    public function apply(Builder $query): Builder
    {
        if ($this->name) {
            $query->where('name', 'like', '%' . $this->name . '%');
        }

        if ($this->allowedScopes) {
            $query->whereJsonContains('allowed_scopes', $this->allowedScopes);
        }

        if ($this->active !== null) {
            $query->where('active', $this->active);
        }

        return $query;
    }
}
