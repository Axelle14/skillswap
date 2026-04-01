<?php
// app/Views/services/browse.php
$pageTitle = 'Browse Services';
use App\Core\Validator;
use App\Middleware\Auth;
require APP_ROOT . '/app/Views/layouts/header.php';

$categories = ['Design','Tech','Writing','Photography','Tutoring','Home Services','Music','Other'];
$catIcons   = ['Design'=>'🎨','Tech'=>'💻','Writing'=>'📝','Photography'=>'📸',
               'Tutoring'=>'🎓','Home Services'=>'🌿','Music'=>'🎵','Other'=>'✨'];
$currentCat = $category ?? '';
?>

<div class="page-wrap">

  <div class="section-header">
    <div>
      <h1 class="section-title">Testing <em>Services</em></h1>
      <p class="section-sub"><?= number_format($total) ?> services available</p>
    </div>
    <?php if (Auth::check()): ?>
      <button class="btn btn-primary" onclick="openModal('addService')">+ List a Service</button>
    <?php else: ?>
      <a href="<?= APP_BASE ?>/register" class="btn btn-primary">Join to List</a>
    <?php endif; ?>
  </div>

  <!-- Search -->
  <div class="search-bar">
    <span class="search-icon">🔍</span>
    <input type="text" id="searchInput" placeholder="Search services, skills, providers…"
           value="<?= Validator::e($search ?? '') ?>"
           oninput="onSearch()">
  </div>

  <!-- Category chips -->
  <div class="filter-chips">
    <button class="chip <?= !$currentCat ? 'active' : '' ?>"
            data-cat="" onclick="setCategory('', this)">All</button>
    <?php foreach ($categories as $cat): ?>
      <button class="chip <?= $currentCat === $cat ? 'active' : '' ?>"
              data-cat="<?= Validator::e($cat) ?>"
              onclick="setCategory('<?= Validator::e($cat) ?>', this)">
        <?= $catIcons[$cat] ?? '' ?> <?= Validator::e($cat) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <!-- Grid -->
  <?php if (empty($services)): ?>
    <div class="empty-state">
      <div class="empty-icon">🔎</div>
      <p>No services found<?= ($search ?? '') ? ' for "' . Validator::e($search) . '"' : '' ?>.</p>
      <?php if (Auth::check()): ?>
        <button class="btn btn-primary" onclick="openModal('addService')">Be the first to list one</button>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="services-grid">
      <?php foreach ($services as $s): ?>
        <div class="service-card">
          <div class="sc-image"><?= $catIcons[$s['category']] ?? '✨' ?></div>
          <div class="sc-body">
            <div class="sc-category"><?= Validator::e($s['category']) ?></div>
            <h3 class="sc-title"><?= Validator::e($s['title']) ?></h3>
            <p class="sc-desc"><?= Validator::e(mb_substr($s['description'], 0, 110)) ?>…</p>
            <div class="sc-footer">
              <div class="sc-user">
                <div class="sc-avatar">
                  <a href="<?= APP_BASE ?>/users/<?= (int)$s['user_id'] ?>">
                    <?= strtoupper(mb_substr($s['provider_name'], 0, 1)) ?>
                  </a>
                </div>
                <div>
                  <a href="<?= APP_BASE ?>/users/<?= (int)$s['user_id'] ?>" class="sc-name">
                    <?= Validator::e($s['provider_name']) ?>
                  </a>
                  <?php if ($s['avg_rating'] > 0): ?>
                    <div class="stars">
                      <?= str_repeat('★', (int)$s['avg_rating']) ?><?= str_repeat('☆', 5-(int)$s['avg_rating']) ?>
                      <span style="color:var(--gray)">(<?= (int)$s['review_count'] ?>)</span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <span class="sc-credits"><?= (int)$s['credits'] ?> cr</span>
            </div>
            <div style="margin-top:14px">
              <a href="<?= APP_BASE ?>/services/<?= (int)$s['id'] ?>" class="btn btn-outline btn-sm" style="width:100%;text-align:center">
                View Details
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
      <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <?php
            $params = http_build_query(array_filter([
              'q'        => $search ?? '',
              'category' => $currentCat,
              'page'     => $i,
            ]));
          ?>
          <?php if ($i === $page): ?>
            <span class="current"><?= $i ?></span>
          <?php else: ?>
            <a href="<?= APP_BASE ?>/services?<?= $params ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- ── Add Service Modal ── -->
<?php if (Auth::check()): ?>
<div class="modal-overlay" id="modal-addService">
  <div class="modal">
    <h2 class="modal-title">List a Service</h2>
    <p class="modal-sub">Offer your skills and earn credits</p>
    <form onsubmit="submitService(event)" id="serviceForm">
      <div class="form-group">
        <label>Service title</label>
        <input type="text" name="title" placeholder="e.g. Responsive Landing Page Design" required>
      </div>
      <div class="form-group">
        <label>Category</label>
        <select name="category" required>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= Validator::e($cat) ?>"><?= Validator::e($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" placeholder="Describe exactly what you'll provide…" rows="3" required></textarea>
      </div>
      <div class="form-group">
        <label>Credit value (1–500)</label>
        <input type="number" name="credits" min="1" max="500" placeholder="e.g. 25" required>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addService')">Cancel</button>
        <button type="submit" class="btn btn-primary">Publish Listing</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
