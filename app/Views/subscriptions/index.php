<?php
// app/Views/subscriptions/index.php
// Route: GET /subscriptions — add to DashboardController + routes.php
$pageTitle = 'Plans & Pricing';
use App\Core\Validator;
use App\Middleware\Auth;
require APP_ROOT . '/app/Views/layouts/header.php';
$currentPlan = \App\Core\Session::get('user_plan', 'free');
?>

<div class="page-wrap">
  <div class="section-header" style="justify-content:center;flex-direction:column;text-align:center;margin-bottom:48px">
    <h1 class="section-title">Choose your <em>Plan</em></h1>
    <p class="section-sub">Unlock more visibility, swap requests and features</p>
  </div>

  <div class="plans-grid">
    <!-- Free -->
    <div class="plan-card">
      <div class="plan-name">Free</div>
      <div class="plan-price"><sup>$</sup>0<span>/mo</span></div>
      <ul class="plan-features">
        <li>Create profile &amp; listings (up to 3)</li>
        <li>Basic swap requests</li>
        <li>Standard search visibility</li>
        <li>50 starter credits</li>
        <li>Community messaging</li>
      </ul>
      <button class="btn btn-outline" style="margin-top:auto" disabled>
        <?= $currentPlan === 'free' ? 'Current Plan' : 'Downgrade' ?>
      </button>
    </div>

    <!-- Premium (featured) -->
    <div class="plan-card featured">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <div class="plan-name">Premium</div>
        <span style="background:var(--caramel);border-radius:20px;padding:4px 12px;font-size:12px">Popular</span>
      </div>
      <div class="plan-price"><sup>$</sup>12<span style="color:rgba(255,255,255,.5)">/mo</span></div>
      <ul class="plan-features">
        <li>Unlimited listings</li>
        <li>Priority search placement</li>
        <li>Unlimited messaging &amp; requests</li>
        <li>Priority dispute support</li>
        <li>Verification badge</li>
        <li>Monthly 100 bonus credits</li>
      </ul>
      <?php if (Auth::check()): ?>
        <button class="btn btn-primary" style="margin-top:auto" onclick="openModal('upgrade')">
          <?= $currentPlan === 'premium' ? 'Current Plan' : 'Upgrade Now' ?>
        </button>
      <?php else: ?>
        <a href="<?= APP_BASE ?>/register" class="btn btn-primary" style="margin-top:auto;text-align:center">Get Started</a>
      <?php endif; ?>
    </div>

    <!-- Pro -->
    <div class="plan-card">
      <div class="plan-name">Pro</div>
      <div class="plan-price"><sup>$</sup>29<span>/mo</span></div>
      <ul class="plan-features">
        <li>Everything in Premium</li>
        <li>Featured listing slots</li>
        <li>Advanced analytics dashboard</li>
        <li>Lower platform fee on add-ons</li>
        <li>Dedicated account manager</li>
        <li>Monthly 250 bonus credits</li>
      </ul>
      <?php if (Auth::check()): ?>
        <button class="btn btn-outline" style="margin-top:auto" onclick="openModal('upgrade')">
          <?= $currentPlan === 'pro' ? 'Current Plan' : 'Get Pro' ?>
        </button>
      <?php else: ?>
        <a href="<?= APP_BASE ?>/register" class="btn btn-outline" style="margin-top:auto;text-align:center">Get Started</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- FAQ -->
  <div style="max-width:600px;margin:60px auto 0;text-align:center">
    <h2 class="section-title" style="font-size:28px;margin-bottom:20px">How credits work</h2>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;text-align:left">
      <?php $faqs = [
        ['🎁','50 starter credits', 'Every new member gets 50 free credits to request services immediately.'],
        ['💸','Earn by giving',     'Provide a service and credits are released to you when the requester confirms completion.'],
        ['🔒','Escrow protection',  'Credits are locked in escrow when you request, so providers are always paid.'],
        ['🔄','No expiry',          'Credits never expire. Accumulate them over time as you provide more services.'],
      ]; ?>
      <?php foreach ($faqs as [$icon, $title, $desc]): ?>
        <div class="card card-sm">
          <div style="font-size:22px;margin-bottom:8px"><?= $icon ?></div>
          <div style="font-weight:500;font-size:14px;margin-bottom:4px"><?= $title ?></div>
          <div style="font-size:13px;color:var(--gray);line-height:1.55"><?= $desc ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Upgrade Modal (placeholder — wire to payment in production) -->
<?php if (Auth::check()): ?>
<div class="modal-overlay" id="modal-upgrade">
  <div class="modal">
    <h2 class="modal-title">Upgrade Plan</h2>
    <p class="modal-sub">Payment integration coming soon — contact us to upgrade manually</p>
    <div style="background:var(--cream);border-radius:var(--r-sm);padding:16px;font-size:14px;color:var(--gray);text-align:center">
      📧 Email <a href="mailto:billing@skillswap.local" style="color:var(--caramel)">billing@skillswap.local</a> to upgrade your plan.
    </div>
    <div class="modal-actions">
      <button class="btn btn-primary" onclick="closeModal('upgrade')">Got it</button>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
