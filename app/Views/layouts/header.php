<?php
use App\Core\{CSRF, Validator};
use App\Middleware\Auth;
$csrf        = CSRF::generate();
$loggedIn    = Auth::check();
$userName    = $loggedIn ? \App\Core\Session::get('user_name') : '';
$initial     = $userName ? strtoupper($userName[0]) : '';
$base        = APP_BASE;
$uri         = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path        = $base ? (str_starts_with($uri, $base) ? substr($uri, strlen($base)) : $uri) : $uri;
$path        = '/' . ltrim($path, '/');
function nav_active(string $check, string $path): string { return $path === $check ? 'active' : ''; }
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Validator::e($pageTitle ?? 'SkillSwap') ?> — SkillSwap</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?>/css/app.css">
<meta id="csrfMeta"  name="csrf-token" content="<?= Validator::e($csrf) ?>">
<meta id="appBase"   name="app-base"   content="<?= Validator::e($base) ?>">
</head>
<body>
<nav class="main-nav">
  <a href="<?= $base ?>/" class="logo"><img src="<?= $base ?>/img/logo.png" alt="SkillSwap"></a>
  <ul class="nav-links">
    <li><a href="<?= $base ?>/services" class="<?= nav_active('/services',$path) ?>">Browse</a></li>
    <?php if ($loggedIn): ?>
    <li><a href="<?= $base ?>/dashboard"     class="<?= nav_active('/dashboard',$path) ?>">Dashboard</a></li>
    <li><a href="<?= $base ?>/messages"      class="<?= nav_active('/messages',$path) ?>">Messages</a></li>
    <li><a href="<?= $base ?>/subscriptions" class="<?= nav_active('/subscriptions',$path) ?>">Plans</a></li>
    <?php endif; ?>
  </ul>
  <div class="nav-right">
    <?php if ($loggedIn): ?>
      <a href="<?= $base ?>/dashboard" class="credit-badge">Credits: <span id="navCredits"><?= (int)\App\Core\Session::get('user_credits',0) ?></span></a>
      <div class="nav-avatar" onclick="toggleUserMenu()"><?= Validator::e($initial) ?></div>
      <div class="user-menu" id="userMenu">
        <a href="<?= $base ?>/profile">My Profile</a>
        <a href="<?= $base ?>/dashboard">Dashboard</a>
        <a href="<?= $base ?>/services">Browse</a>
        <hr>
        <form method="POST" action="<?= $base ?>/logout">
          <input type="hidden" name="_csrf_token" value="<?= Validator::e($csrf) ?>">
          <button type="submit">Sign Out</button>
        </form>
      </div>
    <?php else: ?>
      <a href="<?= $base ?>/login"    class="btn-ghost-sm">Log In</a>
      <a href="<?= $base ?>/register" class="btn-primary-sm">Join Free</a>
    <?php endif; ?>
  </div>
</nav>
<?php $fe = \App\Core\Session::getFlash('error'); $fs = \App\Core\Session::getFlash('success'); ?>
<?php if ($fe): ?><div class="flash flash-error"><span><?= Validator::e($fe) ?></span><button onclick="this.parentElement.remove()">✕</button></div><?php endif; ?>
<?php if ($fs): ?><div class="flash flash-success"><span><?= Validator::e($fs) ?></span><button onclick="this.parentElement.remove()">✕</button></div><?php endif; ?>
