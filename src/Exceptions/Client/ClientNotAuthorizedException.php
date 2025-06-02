<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions\Client;

class ClientNotAuthorizedException extends \Exception
{
    public function __construct(public string $scope)
    {
        parent::__construct(__('O cliente não está autorizado a realizar esta ação: :scope.', ['scope' => $scope]));
    }
}
