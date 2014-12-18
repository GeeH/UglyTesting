<?php
ini_set('error_reporting', E_ALL);

chdir(__DIR__ . '/../../../');

if (file_exists('vendor/autoload.php')) {
    $loader = require 'vendor/autoload.php';
}


if (!isset($loader)) {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you install via composer?');
}

$loader->add('UglyTesting\\', 'module/UglyTesting/src');
$loader->add('UglyTestingTest\\', './module/UglyTesting/test');