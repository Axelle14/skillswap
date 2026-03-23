<?php
// app/Core/Session.php
declare(strict_types=1);

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;

        $secure   = Env::get('SESSION_SECURE', 'false') === 'true';
        $lifetime = (int) Env::get('SESSION_LIFETIME', '7200');
        $name     = Env::get('SESSION_NAME', 'ss_session');

        // Harden session cookie
        session_name($name);
        session_set_cookie_params([
            'lifetime' => 0,          // expire on browser close; JWT handles persistence
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,       // no JS access
            'samesite' => 'Strict',
        ]);

        ini_set('session.gc_maxlifetime', (string)$lifetime);
        ini_set('session.use_strict_mode', '1');       // reject uninitialized IDs
        ini_set('session.use_only_cookies', '1');       // no session ID in URL
        ini_set('session.cookie_httponly', '1');
        ini_set('session.sid_length', '64');
        ini_set('session.sid_bits_per_character', '6');

        session_start();

        // Regenerate ID periodically to prevent fixation
        if (!isset($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = true;
            $_SESSION['_last_regen'] = time();
        } elseif ((time() - ($_SESSION['_last_regen'] ?? 0)) > 900) {
            // Every 15 min
            session_regenerate_id(true);
            $_SESSION['_last_regen'] = time();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
