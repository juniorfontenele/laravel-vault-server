<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Data\Key;

use Illuminate\Contracts\Support\Arrayable;

class KeyData implements Arrayable
{
    public function __construct(
        public string $key_id,
        public int $version,
        public string $public_key,
        public string $client_id,
        public string $valid_from,
        public string $valid_until,
    ) {
        //
    }

    public function toArray(): array
    {
        return [
            'key_id' => $this->key_id,
            'version' => $this->version,
            'public_key' => $this->public_key,
            'client_id' => $this->client_id,
            'valid_from' => $this->valid_from,
            'valid_until' => $this->valid_until,
        ];
    }
}
