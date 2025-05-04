<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\DTOs\Key;

class CreateKeyDTO
{
    public function __construct(
        public readonly string $clientId,
        public readonly int $days = 365,
    ) {
    }

    /**
     * @return array{client_id: string, days: int}
     */
    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'days' => $this->days,
        ];
    }
}
