<?php
// app/Middleware/Auth.php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

class Auth
{
    public static function requireLogin(): void
    {
        if (!Session::has('user_id')) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: ' . APP_BASE . '/login');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (Session::get('user_role') !== 'admin') {
            http_response_code(403);
            exit('Access denied.');
        }
    }

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        $id = Session::get('user_id');
        return $id ? (int)$id : null;
    }

    public static function role(): ?string
    {
        return Session::get('user_role');
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true); // prevent session fixation
        Session::set('user_id',    (int)$user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_name',  $user['full_name']);
        Session::set('user_role',  $user['role']);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function redirectIfLoggedIn(string $to = '/dashboard'): void
    {
        if (self::check()) {
            header("Location: " . APP_BASE . $to);
            exit;
        }
    }
}
