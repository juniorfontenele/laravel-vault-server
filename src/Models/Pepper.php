<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Models;

use Database\Factories\PepperFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JuniorFontenele\LaravelVaultServer\Concerns\AsAuditable;

/** @property-read string $id
 * @property int $version
 * @property string $value
 * @property bool $is_revoked
 * @property \Carbon\CarbonImmutable|null $revoked_at
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<Hash> $hashes
 */
class Pepper extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;
    use HasUuids;
    use AsAuditable;

    /** @var list<string> */
    protected $fillable = [
        'value',
    ];

    protected $hidden = [
        'value',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_revoked' => 'boolean',
            'revoked_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function getTable(): string
    {
        return config('vault.migrations.table_prefix', 'vault_') . 'peppers';
    }

    protected static function newFactory(): PepperFactory
    {
        return PepperFactory::new();
    }

    /** @return HasMany<Hash> */
    public function hashes(): HasMany
    {
        return $this->hasMany(Hash::class, 'pepper_id');
    }

    public function isRevoked(): bool
    {
        return $this->is_revoked;
    }
}
