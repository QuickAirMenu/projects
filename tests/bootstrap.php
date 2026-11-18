<?php

$configCache = __DIR__.'/../bootstrap/cache/config.php';

if (is_file($configCache)) {
    @unlink($configCache);
}

require __DIR__.'/../vendor/autoload.php';
