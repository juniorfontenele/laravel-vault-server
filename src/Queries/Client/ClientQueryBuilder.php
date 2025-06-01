<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Queries\Client;

use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Queries\AbstractQueryBuilder;

class ClientQueryBuilder extends AbstractQueryBuilder
{
    public function __construct()
    {
        parent::__construct(
            Client::class,
            ['*'],
        );
    }
}
