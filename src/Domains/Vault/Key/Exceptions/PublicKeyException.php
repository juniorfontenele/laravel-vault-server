<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Exceptions;

use JuniorFontenele\LaravelVaultServer\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Shared\Traits\HasTranslations;

class PublicKeyException extends \Exception implements Translatable
{
    use HasTranslations;

    public static function invalidPublicKey(): static
    {
        return static::withTranslation('A chave pública não é válida.');
    }
}
