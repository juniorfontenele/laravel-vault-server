<?php

declare(strict_types = 1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;
use RectorLaravel\Rector\MethodCall\ContainerBindConcreteWithClosureOnlyRector;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

return static function (RectorConfig $rectorConfig): void {
    // Paths to analyze
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/tests',
    ]);

    // Skip specific rules
    $rectorConfig->skip([
        CompactToVariablesRector::class,
        ContainerBindConcreteWithClosureOnlyRector::class,
    ]);

    // Enable caching for Rector
    $rectorConfig->cacheDirectory(__DIR__ . '/storage/rector');
    $rectorConfig->cacheClass(FileCacheStorage::class);

    // Apply sets for Laravel and general code quality
    $rectorConfig->sets([
        LaravelLevelSetList::UP_TO_LARAVEL_120,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        SetList::CODE_QUALITY,
    ]);

    // Define PHP version for Rector
    $rectorConfig->phpVersion(PhpVersion::PHP_84);
};
