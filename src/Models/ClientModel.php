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

class ClientModel extends Model
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
            'provisioned_at' => 'datetime',
            'provision_token' => 'hashed',
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

    /** @return HasMany<KeyModel> */
    public function keys(): HasMany
    {
        return $this->hasMany(KeyModel::class, 'client_id');
    }

    /** @return HasOne<KeyModel> */
    public function key(): HasOne
    {
        return $this->hasOne(KeyModel::class, 'client_id')
            ->orderByDesc('version')
            ->valid()
            ->latest('valid_from');
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

    #[Scope]
    protected function provisioned(Builder $query): void
    {
        $query->whereNotNull('provision_token');
    }

    #[Scope]
    protected function unprovisioned(Builder $query): void
    {
        $query->whereNull('provision_token');
    }
}
