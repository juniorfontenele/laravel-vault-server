<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\InvalidScopeException;

class AllowedScopes
{
    /**
     * @var Scope[]
     */
    protected array $scopes = [];

    protected string $separator = ' ';

    /**
     * @param Scope[] $scopes
     */
    final public function __construct(array $scopes)
    {
        foreach ($scopes as $scope) {
            if (! $scope instanceof Scope) { // @phpstan-ignore-line
                throw InvalidScopeException::invalidType();
            }
        }

        $this->scopes = $scopes;
    }

    /**
     * @return Scope[]
     */
    public function all(): array
    {
        return $this->scopes;
    }

    public function has(Scope|string $scope): bool
    {
        if (is_string($scope)) {
            $scope = Scope::fromString($scope);
        }

        return in_array($scope, $this->scopes, true);
    }

    /**
     * @param string[] $scopes
     */
    public static function fromStringArray(array $scopes): static
    {
        $scopeEnums = array_map(
            fn ($scope) => Scope::fromString($scope),
            $scopes
        );

        return new static($scopeEnums);
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return array_map(
            fn ($scope) => $scope->value,
            $this->scopes
        );
    }

    public function separator(string $separator): static
    {
        $this->separator = $separator;

        return $this;
    }

    public function __toString(): string
    {
        return implode($this->separator, $this->toArray());
    }
}
