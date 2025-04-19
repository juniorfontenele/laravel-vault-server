<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JuniorFontenele\LaravelVaultServer\Database\Factories\HashFactory;

class Hash extends Model
{
    /** @use HasFactory<HashFactory> */
    use HasFactory;
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'client_id',
        'user_id',
        'hash',
    ];

    protected $hidden = [
        'hash',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            //
        ];
    }

    public function getTable(): string
    {
        return config('vault.migrations.table_prefix', 'vault_') . 'hashes';
    }

    protected static function newFactory(): HashFactory
    {
        return HashFactory::new();
    }

    /** @return BelongsTo<Client> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
