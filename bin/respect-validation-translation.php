#!/usr/bin/env php
<?php

declare(strict_types=1);

use t0mmy742\RespectValidationTranslation\Extractor;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    echo "You should not use this command inside another project.\n";
    echo "Please first clone this project, then start this command again.\n";
    exit;
}

(new Extractor())->extract($_SERVER['argv'][1] ?? null);
