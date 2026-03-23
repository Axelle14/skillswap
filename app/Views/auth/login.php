<?php
$pageTitle = 'Sign In';
use App\Core\{CSRF, Validator};
require APP_ROOT . '/app/Views/layouts/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h1 class="auth-title">Welcome <em>back</em></h1>
    <p class="auth-sub">Sign in to your SkillSwap account</p>
    <?php if ($error ?? null): ?>
      <div class="flash flash-error" style="border-radius:var(--r-sm);margin-bottom:20px">
        <span><?= Validator::e($error) ?></span>
      </div>
    <?php endif; ?>
    <form method="POST" action="<?= APP_BASE ?>/login">
      <input type="hidden" name="_csrf_token" value="<?= Validator::e(CSRF::generate()) ?>">
      <div class="form-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?= Validator::e($_POST['email'] ?? '') ?>" autocomplete="email" required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px">Sign In</button>
    </form>
    <p class="auth-footer">Don't have an account? <a href="<?= APP_BASE ?>/register">Join for free</a></p>
  </div>
</div>
<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
