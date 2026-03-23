<?php
// app/Views/users/profile.php
$pageTitle = $profile['full_name'];
use App\Core\Validator;
use App\Middleware\Auth;
$catIcons = ['Design'=>'🎨','Tech'=>'💻','Writing'=>'📝','Photography'=>'📸','Tutoring'=>'🎓','Home Services'=>'🌿','Music'=>'🎵','Other'=>'✨'];
require APP_ROOT . '/app/Views/layouts/header.php';
?>

<div class="page-wrap" style="max-width:960px">
  <div style="margin-bottom:16px">
    <a href="<?= APP_BASE ?>/services" class="text-muted" style="font-size:14px">← Browse Services</a>
  </div>

  <!-- Profile header -->
  <div style="background:var(--charcoal);border-radius:var(--r);padding:40px 48px;color:white;
              display:grid;grid-template-columns:auto 1fr auto;gap:32px;align-items:center;margin-bottom:28px;position:relative;overflow:hidden">
    <div style="position:absolute;width:300px;height:300px;border-radius:50%;
                background:radial-gradient(circle,rgba(184,134,78,.2),transparent 70%);
                top:-80px;right:-80px;pointer-events:none"></div>
    <div style="width:80px;height:80px;border-radius:50%;
                background:linear-gradient(135deg,var(--caramel),var(--tan));
                display:flex;align-items:center;justify-content:center;
                font-family:'Cormorant Garamond',serif;font-size:32px">
      <?= strtoupper(mb_substr($profile['full_name'], 0, 1)) ?>
    </div>
    <div>
      <h1 style="font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:300;margin-bottom:6px">
        <?= Validator::e($profile['full_name']) ?>
      </h1>
      <?php if (!empty($profile['skills'])): ?>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px">
          <?php foreach (array_slice(explode(',', $profile['skills']), 0, 5) as $skill): ?>
            <span style="background:rgba(255,255,255,.1);border-radius:20px;padding:4px 12px;font-size:12px">
              <?= Validator::e(trim($skill)) ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div style="display:flex;gap:24px;font-size:13px;color:rgba(255,255,255,.6)">
        <span>
          <?= ucfirst(Validator::e($profile['availability'])) ?>
        </span>
        <?php if ($profile['avg_rating']): ?>
          <span style="color:var(--tan)">
            <?= str_repeat('★', (int)$profile['avg_rating']) ?> <?= number_format((float)$profile['avg_rating'],1) ?>
          </span>
        <?php endif; ?>
        <span><?= (int)$profile['swaps_done'] ?> swaps completed</span>
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:8px;text-align:center">
      <span class="badge badge-<?= Validator::e($profile['subscription_plan']) ?>"><?= ucfirst(Validator::e($profile['subscription_plan'])) ?></span>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px">
    <!-- Services -->
    <div>
      <h2 class="section-title" style="font-size:26px;margin-bottom:18px">Services</h2>
      <?php if (empty($services)): ?>
        <div class="empty-state"><p>No active listings.</p></div>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:14px">
          <?php foreach ($services as $s): ?>
            <a href="<?= APP_BASE ?>/services/<?= (int)$s['id'] ?>" class="card card-sm" style="text-decoration:none;display:block;transition:transform .2s,box-shadow .2s" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='var(--shadow)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
              <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:24px"><?= $catIcons[$s['category']] ?? '✨' ?></span>
                <div style="flex:1;min-width:0">
                  <div style="font-weight:500;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    <?= Validator::e($s['title']) ?>
                  </div>
                  <div style="font-size:12px;color:var(--gray)"><?= Validator::e($s['category']) ?></div>
                </div>
                <span class="sc-credits"><?= (int)$s['credits'] ?> cr</span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Reviews -->
    <div>
      <h2 class="section-title" style="font-size:26px;margin-bottom:18px">Reviews</h2>
      <?php if (empty($reviews)): ?>
        <div class="empty-state"><p>No reviews yet.</p></div>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:14px">
          <?php foreach ($reviews as $r): ?>
            <div class="card card-sm">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <div style="font-size:14px;font-weight:500"><?= Validator::e($r['reviewer_name']) ?></div>
                <span style="color:var(--caramel);font-size:14px"><?= str_repeat('★', (int)$r['rating']) ?></span>
              </div>
              <div style="font-size:12px;color:var(--gray);margin-bottom:6px">
                Re: <?= Validator::e($r['service_title']) ?>
              </div>
              <p style="font-size:14px;color:var(--charcoal);line-height:1.55">
                <?= Validator::e($r['comment']) ?>
              </p>
              <div style="font-size:11px;color:var(--light);margin-top:6px">
                <?= date('M j, Y', strtotime($r['created_at'])) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
