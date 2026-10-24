<?php
define("APP_INIT", true);
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

$pageTitle = "Practical 4: JavaScript DOM Manipulation, Event Handling, and UI Interactivity - StudentHub";
$activePage = "practicals";
$breadcrumbs = ["Practicals Hub" => "index.php", "Practical 4" => ""];

require_once __DIR__ . "/../pages/includes/header.php";
?>

<div class="container" style="max-width:900px; padding-top:20px; margin-bottom:70px;">
  <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
    <div>
      <span class="badge badge-primary">Practical #4 &bull; CO3, CO4</span>
      <h1 style="font-size:2.2rem; font-weight:800; margin-top:8px;">JavaScript DOM Manipulation, Event Handling, and UI Interactivity</h1>
      <p style="color:var(--text-muted);">4 hours implementation | 6 hours engagement</p>
    </div>
    <div style="display:flex; gap:10px;">
      <a href="../pages/faq.php" class="btn btn-primary">🚀 Live Demo in Website &rarr;</a>
      <a href="index.php" class="btn btn-secondary">&larr; Back to Hub</a>
    </div>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:8px;">📋 Problem Definition</h3>
    <p style="line-height:1.8; color:var(--text-main);">Add dynamic UI components such as collapsible FAQ, modal popup, image/content slider, notification banner, hamburger menu, and light/dark theme switcher using JavaScript.</p>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:14px;">🌟 Extension Implementations</h3>
    <div style="margin-bottom:14px;">
      <strong>Intermediate Extension:</strong>
      <p style="color:var(--text-muted); margin-top:4px;">Smooth CSS cubic-bezier transitions added to cards, buttons, and sliding panels.</p>
    </div>
    <div>
      <strong>Advanced Extension:</strong>
      <p style="color:var(--text-muted); margin-top:4px;">Restores user UI preferences and theme on page load automatically.</p>
    </div>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:18px;">🎓 Key Questions, Analysis & Viva Defense</h3>
    
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: How are DOM elements selected and modified?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> Using document.getElementById(), document.querySelectorAll(), classList.toggle(), style.transform, and innerHTML updates.</p>
    </div>
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: Are event listeners attached correctly?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> Event listeners are bound using element.addEventListener() inside DOMContentLoaded to avoid premature DOM execution errors.</p>
    </div>
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: Is localStorage used for remembering theme choice?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> Yes. localStorage.getItem(&quot;studenthub_theme&quot;) persists user preference across browser restarts.</p>
    </div>
  </div>

  <div style="text-align:center; padding:24px; background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius);">
    <h3 style="margin-bottom:10px;">Ready to inspect this feature in action?</h3>
    <a href="../pages/faq.php" class="btn btn-primary btn-lg">Launch Practical 4 Demo &rarr;</a>
  </div>
</div>

<?php require_once __DIR__ . "/../pages/includes/footer.php"; ?>