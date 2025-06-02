<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Queries\Key;

use JuniorFontenele\LaravelVaultServer\Models\Key;
use JuniorFontenele\LaravelVaultServer\Queries\AbstractQueryBuilder;

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
