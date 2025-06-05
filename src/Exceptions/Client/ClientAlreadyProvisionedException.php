<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions\Client;

class ClientAlreadyProvisionedException extends \Exception
{
    public function __construct(string $message = 'Client already provisioned.')
    {
        parent::__construct($message);
    }
}
