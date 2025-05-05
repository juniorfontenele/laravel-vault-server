<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\DTOs\Hash;

class CreateHashDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $hash,
    ) {
        //
    }
}
