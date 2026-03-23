<?php
// app/Views/dashboard/profile.php
$pageTitle = 'My Profile';
use App\Core\{CSRF, Validator};
require APP_ROOT . '/app/Views/layouts/header.php';
?>

<div class="page-wrap" style="max-width:900px">
  <div class="section-header">
    <div>
      <h1 class="section-title">My <em>Profile</em></h1>
      <p class="section-sub">Manage your public presence</p>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:300px 1fr;gap:28px;align-items:start">
    <!-- Public preview -->
    <div style="background:var(--charcoal);border-radius:var(--r);padding:36px 28px;text-align:center;color:white;position:relative;overflow:hidden">
      <div style="position:absolute;width:200px;height:200px;border-radius:50%;
                  background:radial-gradient(circle,rgba(184,134,78,.2),transparent 70%);
                  bottom:-60px;right:-60px;pointer-events:none"></div>
      <div style="width:80px;height:80px;border-radius:50%;
                  background:linear-gradient(135deg,var(--caramel),var(--tan));
                  margin:0 auto 18px;display:flex;align-items:center;justify-content:center;
                  font-family:'Cormorant Garamond',serif;font-size:32px;color:white">
        <?= strtoupper(mb_substr($user['full_name'], 0, 1)) ?>
      </div>
      <div style="font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:300;margin-bottom:6px">
        <?= Validator::e($user['full_name']) ?>
      </div>
      <div style="font-size:12px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:1px;margin-bottom:20px">
        <?= ucfirst(Validator::e($user['subscription_plan'])) ?> · <?= ucfirst(Validator::e($user['availability'])) ?>
      </div>
      <?php if (!empty($user['skills'])): ?>
        <div style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;margin-bottom:20px">
          <?php foreach (explode(',', $user['skills']) as $skill): ?>
            <span style="background:rgba(255,255,255,.1);border-radius:20px;padding:4px 12px;font-size:12px">
              <?= Validator::e(trim($skill)) ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if ($user['avg_rating']): ?>
        <div style="color:var(--tan);font-size:14px;margin-bottom:8px">
          <?= str_repeat('★', (int)$user['avg_rating']) ?><?= str_repeat('☆', 5-(int)$user['avg_rating']) ?>
          <?= number_format((float)$user['avg_rating'], 1) ?>
        </div>
      <?php endif; ?>
      <div style="display:flex;gap:20px;justify-content:center;margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.1)">
        <div>
          <div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:300"><?= (int)$user['swaps_done'] ?></div>
          <div style="font-size:10px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px">Swaps</div>
        </div>
        <div>
          <div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:300;color:var(--tan)"><?= (int)$user['credits'] ?></div>
          <div style="font-size:10px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px">Credits</div>
        </div>
      </div>
    </div>

    <!-- Edit form -->
    <div class="card">
      <h2 class="card-title">Edit Profile</h2>

      <form method="POST" action="<?= APP_BASE ?>/profile/update">
        <input type="hidden" name="_csrf_token" value="<?= Validator::e(CSRF::generate()) ?>">

        <div class="form-group">
          <label>Full name</label>
          <input type="text" name="full_name" value="<?= Validator::e($user['full_name']) ?>" required>
        </div>
        <div class="form-group">
          <label>Bio <span class="text-muted">(optional)</span></label>
          <textarea name="bio" rows="3" placeholder="Tell the community about yourself…"><?= Validator::e($user['bio'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Skills <span class="text-muted">(comma-separated)</span></label>
          <input type="text" name="skills" value="<?= Validator::e($user['skills'] ?? '') ?>"
                 placeholder="e.g. React, Photography, Cooking">
        </div>
        <div class="form-group">
          <label>Availability</label>
          <select name="availability">
            <option value="available"   <?= ($user['availability'] ?? '') === 'available'   ? 'selected' : '' ?>>Available for swaps</option>
            <option value="limited"     <?= ($user['availability'] ?? '') === 'limited'     ? 'selected' : '' ?>>Limited availability</option>
            <option value="unavailable" <?= ($user['availability'] ?? '') === 'unavailable' ? 'selected' : '' ?>>Not available</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </div>
  </div>
</div>

<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
