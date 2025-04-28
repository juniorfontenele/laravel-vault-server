<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Client\Repositories;

use JuniorFontenele\LaravelVaultServer\Domains\Client\Entities\Client;

interface ClientRepositoryInterface
{
    public function save(Client $clientEntity): void;

    public function delete(Client $clientEntity): void;

    public function findById(string $clientId): ?Client;

    /** @return Client[] */
    public function findAll(): array;

    /** @return Client[] */
    public function findAllInactive(): array;

    /** @return Client[] */
    public function findAllActive(): array;

    public function deleteAllInactive(): void;
}
