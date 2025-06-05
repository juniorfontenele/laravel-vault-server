<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions\Client;

class InvalidScopeException extends \Exception
{
    public function __construct(public string $scope)
    {
        parent::__construct(__('Escopo inválido: :scope', ['scope' => $scope]));
    }
}
