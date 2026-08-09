<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // psr/http-message-implementation is a virtual package (implementation marker), it has no classes to detect usage of
    ->ignoreErrorsOnPackage('psr/http-message-implementation', [ErrorType::UNUSED_DEPENDENCY])
    // ext-apcu is optional, so it's intentionally kept in require-dev instead of require
    ->ignoreErrorsOnExtension('ext-apcu', [ErrorType::DEV_DEPENDENCY_IN_PROD]);
