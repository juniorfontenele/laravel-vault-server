<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Models;

use Illuminate\Database\Eloquent\Model;
use JuniorFontenele\LaravelVaultServer\Enums\AuditAction;
use JuniorFontenele\LaravelVaultServer\Facades\VaultAuth;

/**
 * @property-read int $id
 * @property string $action
 * @property string $auditable_type
 * @property string $auditable_id
 * @property string|null $client_id
 * @property array|null $context
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 */
class Audit extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'action',
        'auditable_type',
        'auditable_id',
        'client_id',
        'context',
        'ip_address',
        'user_agent',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'action' => AuditAction::class,
            'context' => 'json',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function getTable(): string
    {
        return config('vault.migrations.table_prefix', 'vault_') . 'audit';
    }

    protected static function booted()
    {
        static::creating(function (self $audit): void {
            $audit->ip_address = request()->ip();
            $audit->user_agent = request()->userAgent();
            $audit->client_id = VaultAuth::key()?->client_id;
        });

        static::deleting(function (self $audit): void {
            throw new \RuntimeException('Audit records cannot be deleted.');
        });

        static::updating(function (self $audit): void {
            throw new \RuntimeException('Audit records cannot be updated.');
        });
    }
}
