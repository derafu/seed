#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Derafu: ETL - From Spreadsheets to Databases Seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

use Derafu\ETL\Command\PipelineCommand;
use Symfony\Component\Console\Application;

$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php', // Package is installed as a dependency.
    __DIR__ . '/../../../autoload.php',  // Package is installed via Composer.
    __DIR__ . '/../autoload.php',        // Fallback.
];

$autoloadPath = null;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        $autoloadPath = $path;
        break;
    }
}

if ($autoloadPath === null) {
    fwrite(STDERR, "Autoloader not found. Please run 'composer install'.\n");
    exit(1);
}

require $autoloadPath;

$application = new Application('Derafu ETL', 'dev-main');
$application->add(new PipelineCommand());
$application->run();
