<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Queries\Hash;

use JuniorFontenele\LaravelVaultServer\Models\HashModel;
use JuniorFontenele\LaravelVaultServer\Queries\AbstractQueryBuilder;

class HashQueryBuilder extends AbstractQueryBuilder
{
    public function __construct()
    {
        parent::__construct(
            modelClass: HashModel::class,
            columns: ['*'],
        );
    }
}
