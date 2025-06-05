<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Queries;

use JuniorFontenele\LaravelVaultServer\Models\Key;

class KeyQueryBuilder extends AbstractQueryBuilder
{
    public function __construct()
    {
        parent::__construct(
            modelClass: Key::class,
            columns: ['*'],
        );
    }
}
