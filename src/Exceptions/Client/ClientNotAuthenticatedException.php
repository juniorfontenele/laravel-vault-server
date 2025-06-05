<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions\Client;

class ClientNotAuthenticatedException extends \Exception
{
    public function __construct(string $message = 'Client is not authenticated.')
    {
        parent::__construct($message);
    }
}
