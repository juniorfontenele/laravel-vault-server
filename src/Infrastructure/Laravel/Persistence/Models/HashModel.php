<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JuniorFontenele\LaravelVaultServer\Database\Factories\HashFactory;

class HashModel extends Model
{
    /** @use HasFactory<HashFactory> */
    use HasFactory;
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'hash',
        'version',
        'is_revoked',
        'revoked_at',
    ];

    protected $hidden = [
        'hash',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_revoked' => 'boolean',
            'revoked_at' => 'datetime',
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

    #[Scope]
    protected function nonRevoked(Builder $query)
    {
        $query->where('is_revoked', false);
    }

    #[Scope]
    protected function revoked(Builder $query)
    {
        $query->where('is_revoked', true);
    }
}
