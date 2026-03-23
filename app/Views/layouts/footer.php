<?php $base = APP_BASE; ?>
<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <img src="<?= $base ?>/img/logo.png" alt="SkillSwap" class="footer-logo">
      <p>Exchange skills, help each other.</p>
    </div>
    <div class="footer-links">
      <a href="<?= $base ?>/services">Browse</a>
      <a href="<?= $base ?>/register">Join Free</a>
      <a href="<?= $base ?>/subscriptions">Plans</a>
    </div>
    <p class="footer-copy">&copy; <?= date('Y') ?> SkillSwap. All rights reserved.</p>
  </div>
</footer>
<div class="toast" id="globalToast" role="status" aria-live="polite"></div>
<script src="<?= $base ?>/js/app.js"></script>
</body>
</html>
