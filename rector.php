<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/_example',
        __DIR__ . '/src',
        __DIR__ . '/test',
    ])
    // uncomment to reach your current PHP version
    ->withSets([\Rector\Set\ValueObject\SetList::PHP_71])
    ->withTypeCoverageLevel(0);
