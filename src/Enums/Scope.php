<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Enums;

use JuniorFontenele\LaravelVaultServer\Exceptions\InvalidScopeException;

enum Scope: string
{
    case KEYS_READ = 'keys:read';
    case KEYS_ROTATE = 'keys:rotate';
    case KEYS_DELETE = 'keys:delete';
    case HASHES_READ = 'hashes:read';
    case HASHES_CREATE = 'hashes:create';
    case HASHES_DELETE = 'hashes:delete';

    public function getLabel(): string
    {
        return match ($this) {
            self::KEYS_READ => 'Read keys',
            self::KEYS_ROTATE => 'Rotate keys',
            self::KEYS_DELETE => 'Delete keys',
            self::HASHES_READ => 'Read hashes',
            self::HASHES_CREATE => 'Create hashes',
            self::HASHES_DELETE => 'Delete hashes',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        $scopes = [];

        foreach (self::cases() as $scope) {
            $scopes[$scope->value] = $scope->getLabel();
        }

        return $scopes;
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? throw InvalidScopeException::invalidScope($value);
    }
}
