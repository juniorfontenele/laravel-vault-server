<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Actions\Client;

use JuniorFontenele\LaravelVaultServer\Data\Client\ClientData;
use JuniorFontenele\LaravelVaultServer\Filters\ClientFilter;
use JuniorFontenele\LaravelVaultServer\Models\ClientModel;

class ListClientsAction
{
    /**
     * Execute the action to list clients based on the provided filter.
     *
     * @param ClientFilter $filter
     * @return ClientData[]
     */
    public function execute(ClientFilter $filter): array
    {
        $query = ClientModel::query();
        $query = $filter->apply($query);

        return $query->get()->map(fn (ClientModel $client): ClientData => ClientData::fromArray($client->toArray()))->toArray();
    }
}
