<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Events\Pepper\PepperRotated;
use JuniorFontenele\LaravelVaultServer\Models\Pepper;
use JuniorFontenele\LaravelVaultServer\Services\PepperService;

beforeEach(function () {
    Pepper::query()->delete();
});

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

describe('PepperService', function () {
    it('rotates pepper and dispatches event', function () {
        Event::fake();
        $service = app(PepperService::class);
        $pepper = $service->rotatePepper();
        expect($pepper)->not()->toBeNull();
        expect($pepper->is_revoked)->toBeFalse();
        Event::assertDispatched(PepperRotated::class);
    });

    it('gets active pepper', function () {
        $service = app(PepperService::class);
        $pepper = $service->rotatePepper();
        $active = $service->getActive();
        expect($active->id)->toBe($pepper->id);
    });
});
