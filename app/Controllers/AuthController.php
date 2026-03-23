<?php
// app/Controllers/AuthController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{CSRF, Session, Validator};
use App\Middleware\{Auth, RateLimiter};
use App\Models\UserModel;

class AuthController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    // GET /login
    public function showLogin(): void
    {
        Auth::redirectIfLoggedIn();
        $error = Session::getFlash('error');
        require APP_ROOT . '/app/Views/auth/login.php';
    }

    // POST /login
    public function login(): void
    {
        Auth::redirectIfLoggedIn();

        $ip  = RateLimiter::getClientIP();
        $key = 'login:' . $ip;

        $v = new Validator($_POST);
        $v->required('email')->email('email')
          ->required('password');

        if ($v->fails()) {
            Session::flash('error', array_values($v->errors())[0]);
            header('Location: ' . APP_BASE . '/login');
            exit;
        }

        // Verify CSRF
        try {
            CSRF::verify($_POST['_csrf_token'] ?? '');
        } catch (\RuntimeException $e) {
            Session::flash('error', 'Security token mismatch. Please try again.');
            header('Location: ' . APP_BASE . '/login');
            exit;
        }

        // Rate limiting
        try {
            $maxHits = (int)\App\Core\Env::get('RATE_LIMIT_LOGIN', '5');
            $window  = (int)\App\Core\Env::get('RATE_LIMIT_WINDOW', '900');
            RateLimiter::check($key, $maxHits, $window);
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            header('Location: ' . APP_BASE . '/login');
            exit;
        }

        $user = $this->users->findByEmail($v->get('email'));

        // Timing-safe: always run verify even if user not found
        $hash = $user['password_hash'] ?? '$2y$12$invalidhashtopreventtimingattack0000000000000000000000';
        $valid = $this->users->verifyPassword($v->raw('password'), $hash);

        if (!$user || !$valid) {
            Session::flash('error', 'Invalid email or password.');
            header('Location: ' . APP_BASE . '/login');
            exit;
        }

        Auth::login($user);
        header('Location: ' . APP_BASE . '/dashboard');
        exit;
    }

    // GET /register
    public function showRegister(): void
    {
        Auth::redirectIfLoggedIn();
        $error = Session::getFlash('error');
        require APP_ROOT . '/app/Views/auth/register.php';
    }

    // POST /register
    public function register(): void
    {
        Auth::redirectIfLoggedIn();

        try {
            CSRF::verify($_POST['_csrf_token'] ?? '');
        } catch (\RuntimeException) {
            Session::flash('error', 'Security token invalid.');
            header('Location: ' . APP_BASE . '/register');
            exit;
        }

        $ip  = RateLimiter::getClientIP();
        try {
            RateLimiter::check('register:' . $ip, 3, 3600);
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            header('Location: ' . APP_BASE . '/register');
            exit;
        }

        $v = new Validator($_POST);
        $v->required('full_name', 'Full name')->min('full_name', 2)->max('full_name', 100)
          ->required('email')->email('email')
          ->required('password')->min('password', 8)->max('password', 128)
          ->required('skills', 'Skills');

        if ($v->fails()) {
            Session::flash('error', array_values($v->errors())[0]);
            header('Location: ' . APP_BASE . '/register');
            exit;
        }

        if ($this->users->emailExists($v->get('email'))) {
            Session::flash('error', 'An account with this email already exists.');
            header('Location: ' . APP_BASE . '/register');
            exit;
        }

        $id   = $this->users->create([
            'full_name' => $v->get('full_name'),
            'email'     => $v->get('email'),
            'password'  => $v->raw('password'),
            'skills'    => $v->get('skills'),
        ]);
        $user = $this->users->findById($id);
        Auth::login($user);
        header('Location: ' . APP_BASE . '/dashboard');
        exit;
    }

    // POST /logout
    public function logout(): void
    {
        try {
            CSRF::verify($_POST['_csrf_token'] ?? '');
        } catch (\RuntimeException) {
            // Still log out — just redirect
        }
        Auth::logout();
        header('Location: ' . APP_BASE . '/login');
        exit;
    }
}
