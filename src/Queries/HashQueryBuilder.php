<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Queries;

use JuniorFontenele\LaravelVaultServer\Models\Hash;

class HashQueryBuilder extends AbstractQueryBuilder
{
    public function __construct()
    {
        parent::__construct(
            modelClass: Hash::class,
            columns: ['*'],
        );
    }
}
