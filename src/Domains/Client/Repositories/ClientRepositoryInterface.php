<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Client\Repositories;

use JuniorFontenele\LaravelVaultServer\Client\Entities\Client;

interface ClientRepositoryInterface
{
    public function save(Client $clientEntity): void;

    public function delete(Client $clientEntity): void;

    public function findById(string $clientId): ?Client;
}
