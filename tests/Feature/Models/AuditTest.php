<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Enums\AuditAction;
use JuniorFontenele\LaravelVaultServer\Models\Audit;
use JuniorFontenele\LaravelVaultServer\Models\Client;

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

beforeEach(function () {
    Audit::query()->delete();
    Client::query()->delete();
});

describe('AuditModel', function () {
    it('creates audit records on model events', function () {
        $client = Client::factory()->create();

        expect(Audit::count())->toBe(1)
            ->and(Audit::first()->action)->toBe(AuditAction::CREATE)
            ->and(Audit::first()->auditable_type)->toBe(Client::class)
            ->and(Audit::first()->auditable_id)->toBe($client->id);

        $retrieved = Client::find($client->id);
        $retrieved->update(['description' => 'changed']);
        $retrieved->delete();

        expect(Audit::count())->toBe(4);
    });

    it('cannot delete audit records', function () {
        $client = Client::factory()->create();
        $audit = Audit::first();

        expect(fn () => $audit->delete())->toThrow(RuntimeException::class, 'Audit records cannot be deleted.');
        expect(Audit::count())->toBe(1);

        expect(fn () => $audit->forceDelete())->toThrow(RuntimeException::class, 'Audit records cannot be deleted.');
        expect(Audit::count())->toBe(1);
    });

    it('cannot update audit records', function () {
        $client = Client::factory()->create();
        $audit = Audit::first();

        expect(fn () => $audit->update(['action' => AuditAction::UPDATE]))->toThrow(RuntimeException::class, 'Audit records cannot be updated.');
        expect(Audit::count())->toBe(1);
        expect(Audit::first()->action)->toBe(AuditAction::CREATE);
    });
});
