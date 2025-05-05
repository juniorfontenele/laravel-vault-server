<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Exceptions;

use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Traits\HasTranslations;

class HashIdException extends \Exception implements Translatable
{
    use HasTranslations;

    public static function invalidHashId(string $hashId): static
    {
        return static::withTranslation('O id :id do hash é inválido.', [
            'id' => $hashId,
        ]);
    }
}
