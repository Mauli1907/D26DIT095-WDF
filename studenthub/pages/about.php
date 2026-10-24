<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$pageTitle = 'About - StudentHub | CHARUSAT FTE';
$activePage = 'about';
$breadcrumbs = ['About' => ''];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Wireframe 2: Page title + portal description -->
<section class="about-hero">
  <div class="container">
    <span class="badge badge-primary" style="margin-bottom:12px;">About StudentHub</span>
    <h1>Charotar University of Science and Technology</h1>
    <p style="max-width:720px; margin:0 auto; font-size:1.15rem; color:var(--text-muted);">
      Faculty of Technology and Engineering (FTE) &bull; Department of Information Technology<br>
      Subject: <strong>Web Development Frameworks (ITUE203)</strong> &bull; Semester 3rd (ODD)
    </p>
  </div>
</section>

<!-- Wireframe 2: Mission, Vision, Coordinator info -->
<section class="container" style="margin-top:40px;">
  <div class="about-values-grid">
    <div class="highlight-card">
      <div class="card-icon">🎯</div>
      <h3>Our Mission</h3>
      <p>
        To deliver an engaging, unified digital experience that simplifies campus communications, promotes active student participation in co-curricular hackathons, and demonstrates enterprise web engineering best practices.
      </p>
    </div>

    <div class="highlight-card">
      <div class="card-icon">🔭</div>
      <h3>Our Vision</h3>
      <p>
        To cultivate future-ready tech engineers with strong competencies in full-stack web standards, accessibility compliance (WCAG 2.1), secure authentication, RESTful APIs, and responsive human-centric user interfaces.
      </p>
    </div>

    <div class="highlight-card">
      <div class="card-icon">💡</div>
      <h3>Core Values</h3>
      <p>
        Excellence in engineering, academic integrity, peer collaboration, and inclusive digital accessibility for every student across disciplines and technical backgrounds.
      </p>
    </div>
  </div>
</section>

<!-- Wireframe 2: Coordinator Info & Faculty Mentors -->
<section class="container" style="margin-top:40px;">
  <div class="section-header">
    <h2>Course Coordinators & Mentors</h2>
    <p>Academic leadership driving the Web Development Frameworks laboratory curriculum.</p>
  </div>

  <div class="team-grid">
    <article class="team-card">
      <img src="../assets/images/default_avatar.svg" alt="Course Coordinator" class="team-avatar">
      <h3 style="font-size:1.2rem; margin-bottom:4px;">Prof. R. K. Patel</h3>
      <p style="color:var(--primary); font-weight:600; font-size:0.9rem; margin-bottom:8px;">Course Coordinator & Associate Professor</p>
      <p style="color:var(--text-muted); font-size:0.875rem;">Department of Information Technology, FTE, CHARUSAT.</p>
      <p style="font-size:0.85rem; color:var(--text-muted); margin-top:8px;">✉️ rkpatel.it@charusat.ac.in</p>
    </article>

    <article class="team-card">
      <img src="../assets/images/default_avatar.svg" alt="Laboratory Mentor" class="team-avatar">
      <h3 style="font-size:1.2rem; margin-bottom:4px;">Dr. Snehal M. Shah</h3>
      <p style="color:var(--primary); font-weight:600; font-size:0.9rem; margin-bottom:8px;">Head of Department</p>
      <p style="color:var(--text-muted); font-size:0.875rem;">Faculty of Technology and Engineering, CHARUSAT.</p>
      <p style="font-size:0.85rem; color:var(--text-muted); margin-top:8px;">✉️ hod.it@charusat.ac.in</p>
    </article>

    <article class="team-card">
      <img src="../assets/images/default_avatar.svg" alt="Assistant Professor" class="team-avatar">
      <h3 style="font-size:1.2rem; margin-bottom:4px;">Prof. Anita Joshi</h3>
      <p style="color:var(--primary); font-weight:600; font-size:0.9rem; margin-bottom:8px;">Laboratory In-Charge</p>
      <p style="color:var(--text-muted); font-size:0.875rem;">Full-Stack Development & Cloud Lab Coordinator.</p>
      <p style="font-size:0.85rem; color:var(--text-muted); margin-top:8px;">✉️ anitajoshi.it@charusat.ac.in</p>
    </article>
  </div>
</section>

<!-- Student Clubs Section (Referenced in Wireframe 1 & 2) -->
<section class="container" id="clubs" style="margin-bottom:70px;">
  <div class="section-header">
    <h2>Student Clubs & Societies</h2>
    <p>Get involved, build leadership skills, and innovate alongside your peers.</p>
  </div>

  <div class="highlights-grid">
    <div class="highlight-card">
      <div class="card-icon">🌐</div>
      <h3>GDSC CHARUSAT</h3>
      <p>Google Developer Student Clubs chapter focusing on Android, Flutter, Web Development, and Google Cloud Solution Challenges.</p>
      <span class="badge badge-primary">Tech Club</span>
    </div>

    <div class="highlight-card">
      <div class="card-icon">🤖</div>
      <h3>RoboTech FTE</h3>
      <p>Building autonomous robotics, micro-controller circuits, drone designs, and participating in national RoboWars championships.</p>
      <span class="badge badge-success">Robotics</span>
    </div>

    <div class="highlight-card">
      <div class="card-icon">🎭</div>
      <h3>Tarang Cultural Club</h3>
      <p>Fostering performing arts, theatrical plays, music bands, street plays, and organizing the grand annual university festival.</p>
      <span class="badge badge-warning">Cultural</span>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
