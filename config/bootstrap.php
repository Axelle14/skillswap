<?php
declare(strict_types=1);

use App\Core\{Env, Session};

// Load .env
Env::load(APP_ROOT . '/.env');

// Base path — auto-detects sub-folder (e.g. /skillswap/public) or root ('')
define('APP_BASE', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

// Error reporting
if (Env::get('APP_DEBUG', 'false') === 'true') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
    ini_set('log_errors', '1');
    ini_set('error_log', APP_ROOT . '/logs/error.log');
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header_remove('X-Powered-By');

// Start session
Session::start();

// Global exception handler
set_exception_handler(function (\Throwable $e): void {
    error_log('Uncaught: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    if (ini_get('display_errors')) {
        echo '<pre style="padding:24px;font-family:monospace;background:#1e1e1e;color:#f1f1f1">';
        echo htmlspecialchars((string)$e);
        echo '</pre>';
    } else {
        echo '<h1>500 — Something went wrong.</h1>';
    }
});
