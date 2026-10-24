<?php
define("APP_INIT", true);
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

$pageTitle = "Practical 10: Secure Login/Logout with Sessions, Role-Based Access, Timeout, and Remember-Me Token - StudentHub";
$activePage = "practicals";
$breadcrumbs = ["Practicals Hub" => "index.php", "Practical 10" => ""];

require_once __DIR__ . "/../pages/includes/header.php";
?>

<div class="container" style="max-width:900px; padding-top:20px; margin-bottom:70px;">
  <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
    <div>
      <span class="badge badge-primary">Practical #10 &bull; CO1, CO5</span>
      <h1 style="font-size:2.2rem; font-weight:800; margin-top:8px;">Secure Login/Logout with Sessions, Role-Based Access, Timeout, and Remember-Me Token</h1>
      <p style="color:var(--text-muted);">2 hours implementation | 6 hours engagement</p>
    </div>
    <div style="display:flex; gap:10px;">
      <a href="../pages/login.php" class="btn btn-primary">🚀 Live Demo in Website &rarr;</a>
      <a href="index.php" class="btn btn-secondary">&larr; Back to Hub</a>
    </div>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:8px;">📋 Problem Definition</h3>
    <p style="line-height:1.8; color:var(--text-main);">Develop secure login and logout functionality using PHP sessions. Verify password using password_verify(), regenerate session ID, restrict dashboard access, role-based redirect, timeout, and remember-me token.</p>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:14px;">🌟 Extension Implementations</h3>
    <div style="margin-bottom:14px;">
      <strong>Intermediate Extension:</strong>
      <p style="color:var(--text-muted); margin-top:4px;">last_login timestamp recorded in users table upon each successful authentication.</p>
    </div>
    <div>
      <strong>Advanced Extension:</strong>
      <p style="color:var(--text-muted); margin-top:4px;">Remember-me functionality stores cryptographically secure 256-bit hash in database with HTTP-only cookie.</p>
    </div>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:18px;">🎓 Key Questions, Analysis & Viva Defense</h3>
    
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: Are sessions started and destroyed securely?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> session_start() uses session.cookie_httponly and session.use_only_cookies. Logout empties $_SESSION, deletes session cookies, and clears tokens.</p>
    </div>
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: Is password_verify() used correctly?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> Yes. password_verify($password, $user[&quot;password_hash&quot;]) safely verifies hashes.</p>
    </div>
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: Is session timeout implemented?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> Yes. Inactivity exceeding 15 minutes (900 seconds) destroys session and redirects to login with alert.</p>
    </div>
  </div>

  <div style="text-align:center; padding:24px; background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius);">
    <h3 style="margin-bottom:10px;">Ready to inspect this feature in action?</h3>
    <a href="../pages/login.php" class="btn btn-primary btn-lg">Launch Practical 10 Demo &rarr;</a>
  </div>
</div>

<?php require_once __DIR__ . "/../pages/includes/footer.php"; ?>