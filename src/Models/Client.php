<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use JuniorFontenele\LaravelVaultServer\Concerns\AsAuditable;
use JuniorFontenele\LaravelVaultServer\Database\Factories\ClientFactory;
use JuniorFontenele\LaravelVaultServer\Enums\Scope as ClientScope;

/**
 * @property-read string $id
 * @property string $name
 * @property string $description
 * @property bool $is_active
 * @property ClientScope[] $allowed_scopes
 * @property string|null $provision_token
 * @property CarbonImmutable|null $provisioned_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read Key|null $key
 * @property-read Key[] $keys
 */
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;
    use HasUuids;
    use AsAuditable;

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
            'provisioned_at' => 'immutable_datetime',
            'provision_token' => 'hashed',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
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
        return $this->hasMany(Key::class, 'client_id');
    }

    /** @return HasOne<Key> */
    public function key(): HasOne
    {
        return $this->hasOne(Key::class, 'client_id')
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
}
