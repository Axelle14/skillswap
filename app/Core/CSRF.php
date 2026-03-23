<?php
// app/Core/CSRF.php
declare(strict_types=1);

namespace App\Core;

class CSRF
{
    private const TOKEN_KEY  = '_csrf_token';
    private const TOKEN_TIME = '_csrf_time';
    private const MAX_AGE    = 3600; // 1 hour

    /**
     * Generate (or reuse) a CSRF token for the current session.
     */
    public static function generate(): string
    {
        self::ensureSession();

        // Rotate if expired
        if (self::isExpired()) {
            self::rotate();
        }

        if (empty($_SESSION[self::TOKEN_KEY])) {
            self::rotate();
        }

        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Verify a submitted token. Throws on failure.
     */
    public static function verify(string $token): void
    {
        self::ensureSession();

        if (
            empty($_SESSION[self::TOKEN_KEY]) ||
            !hash_equals($_SESSION[self::TOKEN_KEY], $token) ||
            self::isExpired()
        ) {
            http_response_code(403);
            throw new \RuntimeException('Invalid or expired CSRF token.');
        }

        // Rotate after successful use (token-per-request)
        self::rotate();
    }

    /** Render a hidden input field. */
    public static function field(): string
    {
        $token = self::generate();
        return sprintf(
            '<input type="hidden" name="_csrf_token" value="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    private static function rotate(): void
    {
        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_TIME] = time();
    }

    private static function isExpired(): bool
    {
        return (time() - ($_SESSION[self::TOKEN_TIME] ?? 0)) > self::MAX_AGE;
    }

    private static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Session not started before CSRF::generate().');
        }
    }
}
