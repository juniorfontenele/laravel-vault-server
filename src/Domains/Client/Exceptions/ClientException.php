<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Client\Exceptions;

use Exception;
use JuniorFontenele\LaravelVaultServer\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Shared\Traits\HasTranslations;

class ClientException extends Exception implements Translatable
{
    use HasTranslations;

    public static function alreadyProvisioned(string $clientId): self
    {
        return static::withTranslation('Cliente :id já foi provisionado', [
            'id' => $clientId,
        ]);
    }
}
