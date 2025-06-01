<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Queries\Client;

use JuniorFontenele\LaravelVaultServer\Models\ClientModel;
use JuniorFontenele\LaravelVaultServer\Queries\AbstractQueryBuilder;

class ClientQueryBuilder extends AbstractQueryBuilder
{
    public function __construct()
    {
        parent::__construct(
            ClientModel::class,
            ['id', 'name', 'description', 'allowed_scopes', 'created_at', 'updated_at'],
        );
    }
}
