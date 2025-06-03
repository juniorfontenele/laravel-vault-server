<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Services;

use Illuminate\Hashing\Argon2IdHasher;
use JuniorFontenele\LaravelVaultServer\Events\Hash\HashDeleted;
use JuniorFontenele\LaravelVaultServer\Events\Hash\HashStored;
use JuniorFontenele\LaravelVaultServer\Events\Hash\HashVerified;
use JuniorFontenele\LaravelVaultServer\Exceptions\Hash\HashStoreException;
use JuniorFontenele\LaravelVaultServer\Models\Hash;
use JuniorFontenele\LaravelVaultServer\Queries\Hash\Filters\HashForUserId;
use JuniorFontenele\LaravelVaultServer\Queries\Hash\HashQueryBuilder;

class HashService
{
    public function __construct(
        private PepperService $pepperService,
        private Argon2IdHasher $hasher,
    ) {
        //
    }

    /**
     * Verify a user's password against the stored hash.
     *
     * @param string $userId The ID of the user.
     * @param string $password The plain text password to verify.
     * @return bool True if the password is valid, false otherwise.
     */
    public function verify(string $userId, string $password): bool
    {
        $hash = (new HashQueryBuilder())
            ->addFilter(new HashForUserId($userId))
            ->build()
            ->first();

        if (! $hash) {
            return false;
        }

        $pepper = $this->pepperService->getById($hash->pepper_id);
        $combinedPassword = $password . $pepper->value;
        $preHash = hash('sha256', $combinedPassword);

        $hashVerified = $this->hasher->check($preHash, $hash->hash);

        event(new HashVerified($userId, $hashVerified));

        return $hashVerified;
    }

    /**
     * Securely store a password for a user.
     *
     * @param string $userId The ID of the user.
     * @param string $password The password to be stored.
     * @return void
     * @throws HashStoreException If the password could not be stored.
     */
    public function store(string $userId, string $password): void
    {
        $pepper = app(PepperService::class)->getActive();
        $combinedPassword = $password . $pepper->value;
        $preHash = hash('sha256', $combinedPassword);
        $hashedPassword = $this->hasher->make($preHash);

        $hash = Hash::updateOrCreate(
            ['user_id' => $userId],
            [
                'hash' => $hashedPassword,
                'pepper_id' => $pepper->id,
                'needs_rehash' => false,
            ],
        );

        if (! $hash) {
            throw new HashStoreException($userId);
        }

        event(new HashStored($userId));
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
