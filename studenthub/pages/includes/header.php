<?php
/**
 * StudentHub Portal - CHARUSAT FTE
 * Practical 2: Semantic HTML5 Pages with Accessibility-Ready Structure
 * Practical 10: Role-Based Dynamic Menu
 */

if (!defined('APP_INIT')) {
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/auth.php';
    require_once __DIR__ . '/../../config/csrf.php';
}

$currentUser = current_user();
$pageTitle = $pageTitle ?? 'StudentHub - CHARUSAT FTE';
$activePage = $activePage ?? '';
$breadcrumbs = $breadcrumbs ?? [];
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="StudentHub - Unified Campus Portal for CHARUSAT Faculty of Technology and Engineering (FTE)">
  <meta name="theme-color" content="#4f46e5">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <!-- Main Stylesheet (Practical 2, 3, 4) -->
  <link rel="stylesheet" href="../assets/css/style.css">
  
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="../assets/images/logo.svg">
</head>
<body>

  <!-- Practical 2 Advanced Extension: Skip to Content link for Keyboard Accessibility -->
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <!-- Practical 4: Top Announcement Banner -->
  <div class="notice-banner" id="announcementBanner" role="region" aria-label="Campus Announcement">
    <span>📢 <strong>Important Update:</strong> Registrations for Spoural TechFest 2026 are now open!</span>
    <a href="events.php">Explore Events →</a>
    <button type="button" class="close-banner" id="closeBannerBtn" aria-label="Dismiss banner">&times;</button>
  </div>

  <!-- Header & Navigation (Wireframe requirement: Header: logo + nav) -->
  <header class="site-header" role="banner">
    <div class="container navbar">
      <a href="index.php" class="brand-logo" aria-label="StudentHub Homepage">
        <img src="../assets/images/logo.svg" alt="StudentHub CHARUSAT FTE Logo" width="160" height="40">
      </a>

      <!-- Dynamic Navigation Menu based on user role (Practical 10 & 14) -->
      <nav role="navigation" aria-label="Main Navigation">
        <ul class="nav-links" id="navLinks">
          <li><a href="index.php" class="<?= $activePage === 'home' ? 'active' : '' ?>">Home</a></li>
          <li><a href="about.php" class="<?= $activePage === 'about' ? 'active' : '' ?>">About</a></li>
          <li><a href="events.php" class="<?= $activePage === 'events' ? 'active' : '' ?>">Events</a></li>
          <li><a href="faq.php" class="<?= $activePage === 'faq' ? 'active' : '' ?>">FAQ</a></li>
          <li><a href="contact.php" class="<?= $activePage === 'contact' ? 'active' : '' ?>">Contact</a></li>
          <li><a href="../practicals/index.php" class="<?= $activePage === 'practicals' ? 'active' : '' ?>" style="color:var(--primary); font-weight:700;">🧪 Practicals Hub</a></li>

          <?php if ($currentUser): ?>
            <?php if ($currentUser['role'] === 'admin'): ?>
              <li><a href="admin_dashboard.php" class="<?= $activePage === 'admin' ? 'active' : '' ?>" style="color:#d97706; font-weight:700;">⚡ Admin Panel</a></li>
            <?php else: ?>
              <li><a href="student_dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">🎓 My Dashboard</a></li>
              <li><a href="profile.php" class="<?= $activePage === 'profile' ? 'active' : '' ?>">👤 Profile</a></li>
            <?php endif; ?>
          <?php endif; ?>
        </ul>
      </nav>

      <div class="nav-actions">
        <!-- Practical 4: Theme Toggle Button -->
        <button type="button" class="theme-toggle-btn" id="themeToggleBtn" aria-label="Toggle theme">
          🌙
        </button>

        <?php if ($currentUser): ?>
          <div style="display:flex; align-items:center; gap:10px;">
            <span style="font-size:0.9rem; font-weight:600; display:none; @media(min-width:600px){display:inline;}">
              Hi, <?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?>
            </span>
            <a href="logout.php" class="btn btn-sm btn-outline" style="border-color:#ef4444; color:#ef4444;">Logout</a>
          </div>
        <?php else: ?>
          <a href="login.php" class="btn btn-sm btn-secondary">Login</a>
          <a href="register.php" class="btn btn-sm btn-primary">Register</a>
        <?php endif; ?>

        <!-- Practical 4: Mobile Hamburger Button -->
        <button type="button" class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle navigation menu" aria-expanded="false">
          ☰
        </button>
      </div>
    </div>
  </header>

  <?php if (!empty($breadcrumbs)): ?>
    <!-- Practical 2 Intermediate Extension: Breadcrumb Navigation -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <div class="container">
        <ol class="breadcrumb-list">
          <li><a href="index.php">Home</a></li>
          <?php foreach ($breadcrumbs as $name => $link): ?>
            <?php if ($link): ?>
              <li><a href="<?= htmlspecialchars($link) ?>"><?= htmlspecialchars($name) ?></a></li>
            <?php else: ?>
              <li class="current" aria-current="page"><?= htmlspecialchars($name) ?></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ol>
      </div>
    </nav>
  <?php endif; ?>

  <?php if ($flash): ?>
    <!-- Flash Notifications Container -->
    <div class="container flash-container">
      <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
        <span><?= htmlspecialchars($flash['message']) ?></span>
        <button type="button" onclick="this.parentElement.remove()" style="background:transparent; border:none; cursor:pointer; font-size:1.1rem; line-height:1;">&times;</button>
      </div>
    </div>
  <?php endif; ?>

  <!-- Main Content Container with ARIA landmark -->
  <main id="main-content" role="main">
