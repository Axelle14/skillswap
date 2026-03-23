<?php
// app/Views/messages/conversation.php
use App\Core\Validator;
use App\Middleware\Auth;
use App\Models\SwapModel;
$pageTitle = 'Chat — ' . ($swap['service_title'] ?? 'Conversation');
$otherName = Auth::id() === (int)$swap['requester_id'] ? $swap['provider_name'] : $swap['requester_name'];
require APP_ROOT . '/app/Views/layouts/header.php';
?>

<div class="page-wrap" style="padding-bottom:0">
  <div style="margin-bottom:12px">
    <a href="<?= APP_BASE ?>/messages" class="text-muted" style="font-size:14px">← All messages</a>
  </div>

  <div class="messages-layout">
    <!-- Left: mini swap info panel -->
    <div class="convo-list">
      <div class="convo-header">Swap Details</div>
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--gray);margin-bottom:4px">Service</div>
          <div style="font-weight:500;font-size:14px"><?= Validator::e($swap['service_title']) ?></div>
        </div>
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--gray);margin-bottom:4px">With</div>
          <div style="font-weight:500;font-size:14px"><?= Validator::e($otherName) ?></div>
        </div>
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--gray);margin-bottom:4px">Status</div>
          <span class="badge badge-<?= Validator::e($swap['status']) ?>">
            <?= ucfirst(str_replace('_',' ',$swap['status'])) ?>
          </span>
        </div>
        <div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--gray);margin-bottom:4px">Credits</div>
          <div style="font-weight:600;color:var(--caramel)"><?= (int)$swap['credits_escrowed'] ?> cr in escrow</div>
        </div>
        <hr style="border:none;border-top:1px solid var(--cream)">
        <!-- Actions -->
        <?php if ($swap['status'] === SwapModel::STATUS_REQUESTED && Auth::id() === (int)$swap['provider_id']): ?>
          <button class="btn btn-primary btn-sm" onclick="swapAction(<?= (int)$swap['id'] ?>, 'accept', this)">✓ Accept Swap</button>
          <button class="btn btn-ghost btn-sm" onclick="swapAction(<?= (int)$swap['id'] ?>, 'decline', this)">✕ Decline</button>
        <?php elseif ($swap['status'] === SwapModel::STATUS_ACCEPTED && Auth::id() === (int)$swap['requester_id']): ?>
          <button class="btn btn-primary btn-sm" onclick="swapAction(<?= (int)$swap['id'] ?>, 'complete', this)">✓ Mark Complete</button>
        <?php elseif ($swap['status'] === SwapModel::STATUS_COMPLETED): ?>
          <button class="btn btn-outline btn-sm" onclick="openReviewModal(<?= (int)$swap['id'] ?>)">★ Leave Review</button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right: chat -->
    <div class="chat-area">
      <div class="chat-header">
        <div class="convo-avatar"><?= strtoupper(mb_substr($otherName, 0, 1)) ?></div>
        <div class="chat-header-info">
          <div class="chat-header-name"><?= Validator::e($otherName) ?></div>
          <div class="chat-header-service"><?= Validator::e($swap['service_title']) ?></div>
        </div>
      </div>

      <div class="chat-messages" id="chatMessages">
        <?php if (empty($thread)): ?>
          <div style="text-align:center;color:var(--gray);font-size:14px;margin:auto;padding:40px">
            No messages yet. Say hello! 👋
          </div>
        <?php else: ?>
          <?php foreach ($thread as $msg): ?>
            <?php $isMe = (int)$msg['sender_id'] === Auth::id(); ?>
            <div class="msg <?= $isMe ? 'me' : 'them' ?>">
              <?php if (!$isMe): ?>
                <div style="font-size:11px;color:var(--gray);margin-bottom:3px;padding:0 3px">
                  <?= Validator::e($msg['sender_name']) ?>
                </div>
              <?php endif; ?>
              <div class="msg-bubble"><?= Validator::e($msg['body']) ?></div>
              <div class="msg-time">
                <?= date('M j, g:i A', strtotime($msg['created_at'])) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if (in_array($swap['status'], [SwapModel::STATUS_ACCEPTED, SwapModel::STATUS_REQUESTED, SwapModel::STATUS_IN_PROGRESS])): ?>
      <div class="chat-input-bar">
        <input type="hidden" id="chatSwapId" value="<?= (int)$swap['id'] ?>">
        <input class="chat-input" id="chatInput" placeholder="Type a message…">
        <button class="send-btn" onclick="sendMessage()">➤</button>
      </div>
      <?php else: ?>
      <div style="padding:14px 24px;background:var(--cream);font-size:13px;color:var(--gray);text-align:center">
        This swap is <?= Validator::e($swap['status']) ?> — messaging is closed.
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Review Modal -->
<div class="modal-overlay" id="modal-review">
  <div class="modal">
    <h2 class="modal-title">Leave a Review</h2>
    <p class="modal-sub">Rate your experience</p>
    <input type="hidden" id="reviewSwapId" value="<?= (int)$swap['id'] ?>">
    <div class="form-group">
      <label>Rating</label>
      <div id="starContainer" style="display:flex;gap:8px;font-size:28px;cursor:pointer">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <span class="star" data-val="<?= $i ?>" style="color:var(--light)">★</span>
        <?php endfor; ?>
      </div>
      <input type="hidden" id="reviewRating" value="5">
    </div>
    <div class="form-group">
      <label>Comment</label>
      <textarea id="reviewComment" rows="3" placeholder="How was the experience?"></textarea>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModal('review')">Cancel</button>
      <button class="btn btn-primary" id="reviewSubmitBtn" onclick="submitReview()">Submit</button>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.star').forEach(star => {
  star.addEventListener('mouseover', () => {
    const v = parseInt(star.dataset.val);
    document.querySelectorAll('.star').forEach(s => s.style.color = parseInt(s.dataset.val) <= v ? 'var(--caramel)' : 'var(--light)');
  });
  star.addEventListener('click', () => { document.getElementById('reviewRating').value = star.dataset.val; });
});
document.getElementById('starContainer')?.addEventListener('mouseleave', () => {
  const v = parseInt(document.getElementById('reviewRating').value);
  document.querySelectorAll('.star').forEach(s => s.style.color = parseInt(s.dataset.val) <= v ? 'var(--caramel)' : 'var(--light)');
});
</script>

<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
