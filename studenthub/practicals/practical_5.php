<?php
define("APP_INIT", true);
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

$pageTitle = "Practical 5: Registration Form with Frontend Validation and User-Friendly Error Handling - StudentHub";
$activePage = "practicals";
$breadcrumbs = ["Practicals Hub" => "index.php", "Practical 5" => ""];

require_once __DIR__ . "/../pages/includes/header.php";
?>

<div class="container" style="max-width:900px; padding-top:20px; margin-bottom:70px;">
  <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
    <div>
      <span class="badge badge-primary">Practical #5 &bull; CO1, CO3</span>
      <h1 style="font-size:2.2rem; font-weight:800; margin-top:8px;">Registration Form with Frontend Validation and User-Friendly Error Handling</h1>
      <p style="color:var(--text-muted);">4 hours implementation | 6 hours engagement</p>
    </div>
    <div style="display:flex; gap:10px;">
      <a href="../pages/register.php" class="btn btn-primary">🚀 Live Demo in Website &rarr;</a>
      <a href="index.php" class="btn btn-secondary">&larr; Back to Hub</a>
    </div>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:8px;">📋 Problem Definition</h3>
    <p style="line-height:1.8; color:var(--text-main);">Create a student registration form with HTML5 input types and JavaScript validation for name, email, mobile number, password, confirm password, course, year, gender, and terms acceptance. Use Regular Expressions for validations.</p>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:14px;">🌟 Extension Implementations</h3>
    <div style="margin-bottom:14px;">
      <strong>Intermediate Extension:</strong>
      <p style="color:var(--text-muted); margin-top:4px;">Real-time instant validation on input and change events.</p>
    </div>
    <div>
      <strong>Advanced Extension:</strong>
      <p style="color:var(--text-muted); margin-top:4px;">Custom CAPTCHA generator created on HTML5 Canvas with random characters, rotation, and line noise.</p>
    </div>
  </div>

  <div class="table-card" style="padding:28px; margin-bottom:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:18px;">🎓 Key Questions, Analysis & Viva Defense</h3>
    
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: Are correct input types and attributes used?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> Input types text, email, tel, password, select, radio, and checkbox with required, maxlength, and pattern attributes.</p>
    </div>
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: Are validation errors displayed near relevant fields?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> Dedicated .error-msg containers appear directly beneath invalid input fields with red borders (.is-invalid).</p>
    </div>
    <div style="margin-bottom:18px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
      <h4 style="margin-bottom:6px; font-size:1.05rem;">❓ Q: Is password strength checked?</h4>
      <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.7;"><strong>Ans:</strong> Yes. Real-time dynamic strength meter computes entropy based on length, uppercase, numbers, and symbols, updating a color-coded bar.</p>
    </div>
  </div>

  <div style="text-align:center; padding:24px; background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius);">
    <h3 style="margin-bottom:10px;">Ready to inspect this feature in action?</h3>
    <a href="../pages/register.php" class="btn btn-primary btn-lg">Launch Practical 5 Demo &rarr;</a>
  </div>
</div>

<?php require_once __DIR__ . "/../pages/includes/footer.php"; ?>