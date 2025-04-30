<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Exceptions;

use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Traits\HasTranslations;

class ClientIdException extends \Exception implements Translatable
{
    use HasTranslations;

    public static function invalidClientId(string $clientId): static
    {
        return static::withTranslation('O id do cliente :id é inválido.', [
            'id' => $clientId,
        ]);
    }
}
