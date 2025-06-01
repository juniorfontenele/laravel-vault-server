<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Actions\Client;

use Illuminate\Database\Eloquent\Collection;
use JuniorFontenele\LaravelVaultServer\Filters\ClientFilter;
use JuniorFontenele\LaravelVaultServer\Models\ClientModel;

class ListClientsAction
{
    /**
     * Execute the action to list clients based on the provided filter.
     *
     * @param ClientFilter $filter
     * @return Collection<ClientModel>
     */
    public function execute(ClientFilter $filter): Collection
    {
        $query = ClientModel::query();
        $query = $filter->apply($query);

        return $query->get();
    }
}
