<?php
$pageTitle = 'Create Account';
use App\Core\{CSRF, Validator};
require APP_ROOT . '/app/Views/layouts/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h1 class="auth-title">Join <em>SkillSwap</em></h1>
    <p class="auth-sub">Start exchanging skills — no payment required</p>
    <?php if ($error ?? null): ?>
      <div class="flash flash-error" style="border-radius:var(--r-sm);margin-bottom:20px">
        <span><?= Validator::e($error) ?></span>
      </div>
    <?php endif; ?>
    <form method="POST" action="<?= APP_BASE ?>/register">
      <input type="hidden" name="_csrf_token" value="<?= Validator::e(CSRF::generate()) ?>">
      <div class="form-group">
        <label>Full name</label>
        <input type="text" name="full_name" value="<?= Validator::e($_POST['full_name'] ?? '') ?>" autocomplete="name" required>
      </div>
      <div class="form-group">
        <label>Email address</label>
        <input type="email" name="email" value="<?= Validator::e($_POST['email'] ?? '') ?>" autocomplete="email" required>
      </div>
      <div class="form-group">
        <label>Password <span class="text-muted">(min. 8 characters)</span></label>
        <input type="password" name="password" autocomplete="new-password" minlength="8" required>
      </div>
      <div class="form-group">
        <label>Your skills</label>
        <input type="text" name="skills" placeholder="e.g. Graphic Design, Python, Photography" value="<?= Validator::e($_POST['skills'] ?? '') ?>" required>
      </div>
      <div style="background:var(--cream);border-radius:var(--r-sm);padding:14px 16px;font-size:13px;color:var(--gray);margin-bottom:20px">
        🎁 You'll receive <strong style="color:var(--charcoal)">50 starter credits</strong> when you join.
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%">Create Account</button>
    </form>
    <p class="auth-footer">Already have an account? <a href="<?= APP_BASE ?>/login">Sign in</a></p>
  </div>
</div>
<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
