<?php

error_reporting(E_ALL);

$autoload = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoload)) {
    throw new RuntimeException(
        '`vendor/autoload.php` could not be found. Did you install via composer?'
    );
}

require $autoload;
