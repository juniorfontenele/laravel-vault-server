<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions\Key;

class ClientKeyNotFoundException extends \Exception
{
    /**
     * Create a new class instance.
     */
    public function __construct(public string $clientId)
    {
        parent::__construct(__('Chave não encontrada para o cliente com ID :id.', [
            'id' => $clientId,
        ]));
    }
}
