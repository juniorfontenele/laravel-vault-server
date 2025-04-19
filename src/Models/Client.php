<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use JuniorFontenele\LaravelVaultServer\Database\Factories\ClientFactory;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'allowed_scopes',
        'provision_token',
    ];

    protected $hidden = [
        'provision_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'allowed_scopes' => 'array',
        ];
    }

    public function getTable(): string
    {
        return config('vault.migrations.table_prefix', 'vault_') . 'clients';
    }

    protected static function newFactory(): ClientFactory
    {
        return ClientFactory::new();
    }

    /** @return HasMany<Key> */
    public function keys(): HasMany
    {
        return $this->hasMany(Key::class);
    }

    /** @return HasOne<Key> */
    public function key(): HasOne
    {
        return $this->hasOne(Key::class)
            ->orderByDesc('version')
            ->valid()
            ->latest('valid_from');
    }

    /** @return HasMany<PrivateKey> */
    public function privateKeys(): HasMany
    {
        return $this->hasMany(PrivateKey::class);
    }

    /** @return HasOne<PrivateKey> */
    public function privateKey(): HasOne
    {
        return $this->hasOne(PrivateKey::class)
            ->orderByDesc('version')
            ->valid()
            ->latest('valid_from');
    }

    /** @return HasMany<Hash> */
    public function hashes(): HasMany
    {
        return $this->hasMany(Hash::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isInactive(): bool
    {
        return ! $this->isActive();
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function inactive(Builder $query): void
    {
        $query->where('is_active', false);
    }
}
