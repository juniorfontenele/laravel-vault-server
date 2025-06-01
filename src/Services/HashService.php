<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Services;

use JuniorFontenele\LaravelVaultServer\Events\Hash\HashDeleted;
use JuniorFontenele\LaravelVaultServer\Events\Hash\HashRetrieved;
use JuniorFontenele\LaravelVaultServer\Events\Hash\HashStored;
use JuniorFontenele\LaravelVaultServer\Exceptions\Hash\HashStoreException;
use JuniorFontenele\LaravelVaultServer\Models\Hash;
use JuniorFontenele\LaravelVaultServer\Queries\Hash\Filters\HashForUserId;
use JuniorFontenele\LaravelVaultServer\Queries\Hash\HashQueryBuilder;

class HashService
{
    /**
     * Retrieve a hash by user ID.
     *
     * @param string $userId The ID of the user.
     * @return Hash|null The hash model if found, null otherwise.
     */
    public function get(string $userId): ?Hash
    {
        $hash = (new HashQueryBuilder())
            ->addFilter(new HashForUserId($userId))
            ->build()
            ->first();

        event(new HashRetrieved($userId));

        return $hash;
    }

    /**
     * Store a hash for a user.
     *
     * @param string $userId The ID of the user.
     * @param string $hash The hash to be stored.
     * @return Hash The stored hash model.
     * @throws HashStoreException If the hash could not be stored.
     */
    public function store(string $userId, string $hash): Hash
    {
        $hash = Hash::updateOrCreate(
            ['user_id' => $userId],
            ['hash' => $hash],
        );

        if (! $hash) {
            throw new HashStoreException($userId);
        }

        event(new HashStored($userId));

        return $hash;
    }

    public function delete(string $userId): void
    {
        (new HashQueryBuilder())
            ->addFilter(new HashForUserId($userId))
            ->build()
            ->delete();

        event(new HashDeleted($userId));
    }
}
