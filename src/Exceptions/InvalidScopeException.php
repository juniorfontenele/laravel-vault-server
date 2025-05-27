<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions;

use InvalidArgumentException;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Traits\HasTranslations;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;

final class InvalidScopeException extends InvalidArgumentException implements Translatable
{
    use HasTranslations;

    public static function invalidType(): static
    {
        return static::withTranslation('O escopo precisa ser do tipo ' . Scope::class);
    }

    public static function invalidScope(string $scope): static
    {
        return static::withTranslation('O escopo :scope não é válido.', ['scope' => $scope]);
    }
}
