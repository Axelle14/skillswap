<?php
// config/routes.php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\{
    AuthController,
    DashboardController,
    ServiceController,
    SwapController,
    MessageController
};

$router = new Router();

// ── Auth ──────────────────────────────────────────────────
$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register',[AuthController::class, 'register']);
$router->post('/logout',  [AuthController::class, 'logout']);

// ── Home / Browse ─────────────────────────────────────────
$router->get('/',          [ServiceController::class, 'index']);
$router->get('/services',  [ServiceController::class, 'index']);
$router->get('/services/:id', [ServiceController::class, 'show']);

// ── Service CRUD ──────────────────────────────────────────
$router->post('/services',           [ServiceController::class, 'create']);
$router->post('/services/:id/edit',  [ServiceController::class, 'update']);
$router->post('/services/:id/delete',[ServiceController::class, 'delete']);

// ── Dashboard & Profile ───────────────────────────────────
$router->get('/dashboard',       [DashboardController::class, 'index']);
$router->get('/profile',         [DashboardController::class, 'profile']);
$router->post('/profile/update', [DashboardController::class, 'updateProfile']);
$router->get('/users/:id',       [DashboardController::class, 'viewUser']);

// ── Swaps ─────────────────────────────────────────────────
$router->post('/swaps/request',      [SwapController::class, 'request']);
$router->get('/swaps/:id',           [SwapController::class, 'show']);
$router->post('/swaps/:id/accept',   [SwapController::class, 'accept']);
$router->post('/swaps/:id/decline',  [SwapController::class, 'decline']);
$router->post('/swaps/:id/complete', [SwapController::class, 'complete']);
$router->post('/swaps/:id/review',   [SwapController::class, 'review']);

// ── Messages ──────────────────────────────────────────────
$router->get('/messages',                [MessageController::class, 'inbox']);
$router->get('/messages/:swap_id',       [MessageController::class, 'conversation']);
$router->post('/messages/send',          [MessageController::class, 'send']);

// ── Subscriptions ────────────────────────────────────────────
$router->get('/subscriptions', [DashboardController::class, 'subscriptions']);

// ── Dispatch ──────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];
$router->dispatch($method, $uri);
