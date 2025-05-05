<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Exceptions;

use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Traits\HasTranslations;

class KeyIdException extends \Exception implements Translatable
{
    use HasTranslations;

    public static function invalidKeyId(string $keyId): static
    {
        return static::withTranslation('O id :id da chave é inválido.', [
            'id' => $keyId,
        ]);
    }
}
