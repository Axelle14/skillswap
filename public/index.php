<?php
declare(strict_types=1);

// Turn on errors during development — remove for production
ini_set('display_errors', '1');
error_reporting(E_ALL);

define('APP_ROOT', dirname(__DIR__));

// Autoloader
spl_autoload_register(function (string $class): void {
    $file = APP_ROOT . '/app/' . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($file)) require_once $file;
});

require_once APP_ROOT . '/config/bootstrap.php';
require_once APP_ROOT . '/config/routes.php';
