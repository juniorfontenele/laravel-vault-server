<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions\Client;

class ClientNotFoundException extends \Exception
{
    public function __construct(string $clientId)
    {
        parent::__construct(__('Cliente com ID :clientId não foi encontrado.', ['clientId' => $clientId]));
    }
}
