<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Queries;

use JuniorFontenele\LaravelVaultServer\Models\Client;

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
