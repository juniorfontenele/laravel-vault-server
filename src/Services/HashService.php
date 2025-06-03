<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Services;

use Illuminate\Hashing\Argon2IdHasher;
use JuniorFontenele\LaravelVaultServer\Events\Hash\HashDeleted;
use JuniorFontenele\LaravelVaultServer\Events\Hash\HashStored;
use JuniorFontenele\LaravelVaultServer\Events\Hash\HashVerified;
use JuniorFontenele\LaravelVaultServer\Events\Hash\RehashNeeded;
use JuniorFontenele\LaravelVaultServer\Exceptions\Hash\HashStoreException;
use JuniorFontenele\LaravelVaultServer\Exceptions\Hash\RehashNeededException;
use JuniorFontenele\LaravelVaultServer\Models\Hash;
use JuniorFontenele\LaravelVaultServer\Queries\Hash\Filters\HashForUserId;
use JuniorFontenele\LaravelVaultServer\Queries\Hash\HashQueryBuilder;

class HashService
{
    public const PREHASH_ALGORITHM = 'sha256';

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
        $dummyHash = $this->hasher->make(bin2hex(random_bytes(16)));

        $hash = (new HashQueryBuilder())
            ->addFilter(new HashForUserId($userId))
            ->build()
            ->first();

        if (! $hash) {
            $dummyPepper = 'dummy_pepper_value';
            $combinedPassword = $password . $dummyPepper;
            $preHash = hash(self::PREHASH_ALGORITHM, $combinedPassword);

            $this->hasher->check($preHash, $dummyHash);

            return false;
        }

        $pepper = $this->pepperService->getById($hash->pepper_id);
        $combinedPassword = $password . $pepper->value;
        $preHash = hash(self::PREHASH_ALGORITHM, $combinedPassword);

        $hashVerified = $this->hasher->check($preHash, $hash->hash);

        event(new HashVerified($userId, $hashVerified));

        if ($hashVerified && $hash->needs_rehash) {
            event(new RehashNeeded($userId, (string) $hash->pepper->version));

            throw new RehashNeededException();
        }

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
        if ($userId === '' || $password === '') {
            throw new HashStoreException($userId);
        }

        $pepper = $this->pepperService->getActive();
        $combinedPassword = $password . $pepper->value;
        $preHash = hash(self::PREHASH_ALGORITHM, $combinedPassword);
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

    /**
     * Delete a user's password hash.
     *
     * @param string $userId The ID of the user whose hash is to be deleted.
     * @return void
     */
    public function delete(string $userId): void
    {
        (new HashQueryBuilder())
            ->addFilter(new HashForUserId($userId))
            ->build()
            ->delete();

        event(new HashDeleted($userId));
    }
}
