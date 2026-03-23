<?php
// app/Views/messages/inbox.php
$pageTitle = 'Messages';
use App\Core\Validator;
require APP_ROOT . '/app/Views/layouts/header.php';
?>

<div class="page-wrap">
  <div class="section-header">
    <div>
      <h1 class="section-title">Messages</h1>
      <p class="section-sub">Your swap conversations</p>
    </div>
  </div>

  <?php if (empty($conversations)): ?>
    <div class="empty-state card">
      <div class="empty-icon">💬</div>
      <p>No conversations yet.</p>
      <a href="<?= APP_BASE ?>/services" class="btn btn-primary">Browse Services</a>
    </div>
  <?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
      <?php foreach ($conversations as $c): ?>
        <a href="<?= APP_BASE ?>/messages/<?= (int)$c['swap_id'] ?>" class="convo-item" style="display:flex;text-decoration:none;color:inherit">
          <div class="convo-avatar"><?= strtoupper(mb_substr($c['other_name'], 0, 1)) ?></div>
          <div class="convo-info">
            <div class="convo-name">
              <?= Validator::e($c['other_name']) ?>
              <?php if ((int)$c['unread'] > 0): ?>
                <span class="unread-dot"></span>
              <?php endif; ?>
            </div>
            <div style="font-size:12px;color:var(--caramel);margin-bottom:2px"><?= Validator::e($c['service_title']) ?></div>
            <div class="convo-preview"><?= Validator::e($c['last_message'] ?? 'No messages yet') ?></div>
          </div>
          <div class="convo-time">
            <?= $c['last_at'] ? date('M j', strtotime($c['last_at'])) : '' ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
