<?php
define("APP_INIT", true);
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

$pageTitle = "Practical 12: Event Management CRUD with Poster Upload and File Validation - StudentHub";
$activePage = "practicals";
$breadcrumbs = ["Practicals Hub" => "index.php", "Practical 12" => ""];

require_once __DIR__ . "/../pages/includes/header.php";
?>

<div class="container" style="max-width:900px; padding-top:20px; margin-bottom:70px;">
  <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
    <div>
      <span class="badge badge-primary">Practical #12 &bull; CO1, CO4, CO5</span>
      <h1 style="font-size:2.2rem; font-weight:800; margin-top:8px;">Event Management CRUD with Poster Upload and File Validation</h1>
      <p style="color:var(--text-muted);">4 hours implementation | 6 hours engagement</p>
    </div>
    <div style="display:flex; gap:10px;">
      <a href="../pages/admin_events.php" class="btn btn-primary">🚀 Live Demo in Website &rarr;</a>
      <a href="index.php" class="btn btn-secondary">&larr; Back to Hub</a>
    </div>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:8px;">📋 Problem Definition</h3>
    <p style="line-height:1.8; color:var(--text-main);">Develop event management functionality for admin users. Admin can add, view, update, delete, and upload event posters with file type and size validation.</p>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:14px;">🌟 Extension Implementations</h3>
    <div style="margin-bottom:14px;">
      <strong>Intermediate Extension:</strong>
      <p style="color:var(--text-muted); margin-top:4px;">Event category filter and status filter (upcoming, ongoing, completed, cancelled).</p>
    </div>
    <div>
      <strong>Advanced Extension:</strong>
      <p style="color:var(--text-muted); margin-top:4px;">Image renaming with uniqid() to prevent collisions, plus directory protection with index.html.</p>
    </div>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:18px;">🎓 Key Questions, Analysis & Viva Defense</h3>
    
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: Are poster uploads validated for type and size?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> Yes. Server checks finfo MIME type (image/jpeg, image/png, image/webp) and enforces 2MB maximum limit.</p>
    </div>
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: Is the UI linked with the database correctly?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> Yes. Events table updates dynamically with new entries, registered counts, and poster links.</p>
    </div>
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: Are success and failure messages displayed?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> Yes. Flash alerts notify user of upload success or specific validation rejections.</p>
    </div>
  </div>

  <div style="text-align:center; padding:24px; background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius);">
    <h3 style="margin-bottom:10px;">Ready to inspect this feature in action?</h3>
    <a href="../pages/admin_events.php" class="btn btn-primary btn-lg">Launch Practical 12 Demo &rarr;</a>
  </div>
</div>

<?php require_once __DIR__ . "/../pages/includes/footer.php"; ?>