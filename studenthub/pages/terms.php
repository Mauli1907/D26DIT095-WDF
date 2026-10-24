<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$pageTitle = 'Terms of Use - StudentHub | CHARUSAT FTE';
$activePage = '';
$breadcrumbs = ['Terms of Use' => ''];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width:840px; padding-top:30px; margin-bottom:70px;">
  <span class="badge badge-primary">Institutional Policy</span>
  <h1 style="font-size:2.2rem; font-weight:800; margin-top:8px; margin-bottom:14px;">Terms of Use & Student Conduct</h1>
  <p style="color:var(--text-muted); margin-bottom:30px;">Last updated: October 2026 &bull; Faculty of Technology & Engineering (FTE)</p>

  <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:36px; line-height:1.8;">
    <h3 style="font-size:1.25rem; margin-bottom:8px;">1. Purpose of StudentHub Portal</h3>
    <p style="color:var(--text-muted); margin-bottom:20px;">
      StudentHub is an official digital platform designed for students of Charotar University of Science and Technology (CHARUSAT) to manage event participation, receive academic notices, and collaborate on technical hackathons.
    </p>

    <h3 style="font-size:1.25rem; margin-bottom:8px;">2. User Account Security</h3>
    <p style="color:var(--text-muted); margin-bottom:20px;">
      Students are solely responsible for maintaining the confidentiality of their credentials. Sharing of passwords, session tokens, or unauthorized penetration testing without prior faculty permission is strictly prohibited.
    </p>

    <h3 style="font-size:1.25rem; margin-bottom:8px;">3. Event Registration Commitment</h3>
    <p style="color:var(--text-muted); margin-bottom:20px;">
      Registering for workshops and hackathons reserves a physical seat. If you are unable to attend, please cancel your registration from your dashboard at least 24 hours in advance so waitlisted students can be accommodated.
    </p>

    <h3 style="font-size:1.25rem; margin-bottom:8px;">4. Academic Honesty</h3>
    <p style="color:var(--text-muted);">
      All code, designs, and submissions created during university-hosted hackathons must adhere to standard academic honesty codes. Plagiarism or impersonation will lead to disciplinary review.
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
