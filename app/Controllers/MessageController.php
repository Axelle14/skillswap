<?php
// app/Controllers/MessageController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{CSRF, Validator};
use App\Middleware\{Auth, RateLimiter};
use App\Models\{MessageModel, SwapModel};

class MessageController
{
    private MessageModel $messages;
    private SwapModel    $swaps;

    public function __construct()
    {
        $this->messages = new MessageModel();
        $this->swaps    = new SwapModel();
    }

    // GET /messages
    public function inbox(): void
    {
        Auth::requireLogin();
        $conversations = $this->messages->getInboxSummary(Auth::id());
        require APP_ROOT . '/app/Views/messages/inbox.php';
    }

    // GET /messages/:swap_id
    public function conversation(array $params): void
    {
        Auth::requireLogin();
        $swapId = (int)$params['swap_id'];

        if (!$this->swaps->canAccess($swapId, Auth::id())) {
            http_response_code(403); exit('Access denied.');
        }

        $swap     = $this->swaps->getSwapWithDetails($swapId);
        $thread   = $this->messages->getConversation($swapId);
        $this->messages->markRead($swapId, Auth::id());

        require APP_ROOT . '/app/Views/messages/conversation.php';
    }

    // POST /messages/send
    public function send(): void
    {
        Auth::requireLogin();

        // Rate limit: 30 messages per minute
        try {
            RateLimiter::check('msg:' . Auth::id(), 30, 60);
        } catch (\RuntimeException $e) {
            $this->jsonError($e->getMessage(), 429);
            return;
        }

        try { CSRF::verify($_POST['_csrf_token'] ?? ''); }
        catch (\RuntimeException) { $this->jsonError('Invalid CSRF token.', 403); return; }

        $v = new Validator($_POST);
        $v->required('swap_id')->integer('swap_id')
          ->required('body')->min('body', 1)->max('body', 2000);

        if ($v->fails()) { $this->jsonError(array_values($v->errors())[0]); return; }

        $swapId = (int)$v->get('swap_id');
        if (!$this->swaps->canAccess($swapId, Auth::id())) {
            $this->jsonError('Access denied.', 403);
            return;
        }

        $id = $this->messages->send($swapId, Auth::id(), $v->get('body'));
        $this->jsonSuccess(['id' => $id, 'message' => 'Message sent.']);
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
