<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JuniorFontenele\LaravelVaultServer\Database\Factories\HashFactory;

/**
 * @property-read string $user_id
 * @property string $hash
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class Hash extends Model
{
    /** @use HasFactory<HashFactory> */
    use HasFactory;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
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
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
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
}
