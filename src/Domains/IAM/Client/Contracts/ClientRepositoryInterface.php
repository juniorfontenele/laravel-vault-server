<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts;

use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;

interface ClientRepositoryInterface
{
    public function save(Client $clientEntity): void;

    public function delete(Client $clientEntity): void;

    public function findClientByClientId(string $clientId): ?Client;

    /** @return Client[] */
    public function findAllClients(): array;

    /** @return Client[] */
    public function findAllInactiveClients(): array;

    /** @return Client[] */
    public function findAllActiveClients(): array;

    public function deleteAllInactiveClients(): void;
}
