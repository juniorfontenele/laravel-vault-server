<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HashResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'user_id' => $this->user_id,
            'hash' => $this->hash,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
