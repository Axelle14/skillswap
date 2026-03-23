<?php
// app/Controllers/ServiceController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{CSRF, Session, Validator};
use App\Middleware\Auth;
use App\Models\ServiceModel;

class ServiceController
{
    private ServiceModel $services;

    public function __construct()
    {
        $this->services = new ServiceModel();
    }

    // GET /services
    public function index(): void
    {
        $search   = htmlspecialchars(trim($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8');
        $category = htmlspecialchars(trim($_GET['category'] ?? ''), ENT_QUOTES, 'UTF-8');
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $limit    = 12;
        $offset   = ($page - 1) * $limit;

        $services = $this->services->browse($search, $category, $limit, $offset);
        $total    = $this->services->countBrowse($search, $category);
        $pages    = (int)ceil($total / $limit);

        require APP_ROOT . '/app/Views/services/browse.php';
    }

    // GET /services/:id
    public function show(array $params): void
    {
        $service = $this->services->findWithOwner((int)$params['id']);
        if (!$service) {
            http_response_code(404);
            require APP_ROOT . '/public/404.php';
            return;
        }
        require APP_ROOT . '/app/Views/services/detail.php';
    }

    // POST /services  (create)
    public function create(): void
    {
        Auth::requireLogin();
        try { CSRF::verify($_POST['_csrf_token'] ?? ''); }
        catch (\RuntimeException) { $this->jsonError('Invalid CSRF token.', 403); return; }

        $allowed = ['Design', 'Tech', 'Writing', 'Photography', 'Tutoring', 'Home Services', 'Music', 'Other'];

        $v = new Validator($_POST);
        $v->required('title')->min('title', 5)->max('title', 150)
          ->required('description')->min('description', 20)->max('description', 1000)
          ->required('category')->in('category', $allowed)
          ->required('credits')->integer('credits')->range('credits', 1, 500);

        if ($v->fails()) {
            $this->jsonError(array_values($v->errors())[0]);
            return;
        }

        $id = $this->services->create(Auth::id(), [
            'title'       => $v->get('title'),
            'description' => $v->get('description'),
            'category'    => $v->get('category'),
            'credits'     => (int)$v->get('credits'),
        ]);

        $this->jsonSuccess(['id' => $id, 'message' => 'Service listed successfully.']);
    }

    // POST /services/:id/edit
    public function update(array $params): void
    {
        Auth::requireLogin();
        try { CSRF::verify($_POST['_csrf_token'] ?? ''); }
        catch (\RuntimeException) { $this->jsonError('Invalid CSRF token.', 403); return; }

        $v = new Validator($_POST);
        $v->required('title')->max('title', 150)
          ->required('description')->max('description', 1000)
          ->required('credits')->integer('credits')->range('credits', 1, 500);

        if ($v->fails()) {
            $this->jsonError(array_values($v->errors())[0]);
            return;
        }

        $ok = $this->services->updateService((int)$params['id'], Auth::id(), [
            'title'       => $v->get('title'),
            'description' => $v->get('description'),
            'category'    => $v->get('category'),
            'credits'     => (int)$v->get('credits'),
        ]);

        $ok ? $this->jsonSuccess(['message' => 'Service updated.'])
            : $this->jsonError('Not found or permission denied.', 403);
    }

    // POST /services/:id/delete
    public function delete(array $params): void
    {
        Auth::requireLogin();
        try { CSRF::verify($_POST['_csrf_token'] ?? ''); }
        catch (\RuntimeException) { $this->jsonError('Invalid CSRF token.', 403); return; }

        $ok = $this->services->deleteOwned((int)$params['id'], Auth::id());
        $ok ? $this->jsonSuccess(['message' => 'Service deleted.'])
            : $this->jsonError('Not found or permission denied.', 403);
    }

    private function jsonSuccess(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, ...$data]);
    }

    private function jsonError(string $message, int $code = 422): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    }
}
