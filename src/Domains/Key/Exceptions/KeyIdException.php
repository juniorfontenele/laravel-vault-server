<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Key\Exceptions;

use JuniorFontenele\LaravelVaultServer\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Shared\Traits\HasTranslations;

class KeyIdException extends \Exception implements Translatable
{
    use HasTranslations;

    public static function invalidKeyId(string $keyId): static
    {
        return static::withTranslation('O id da chave :id é inválido.', [
            'id' => $keyId,
        ]);
    }
}
