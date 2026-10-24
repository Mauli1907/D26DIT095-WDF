<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$pageTitle = 'Home - StudentHub | CHARUSAT FTE';
$activePage = 'home';
$breadcrumbs = []; // Root

// Fetch upcoming events from DB
try {
    $featuredEvents = db_fetch_all("SELECT * FROM events WHERE status = 'upcoming' ORDER BY event_date ASC LIMIT 3");
    $recentNotices = db_fetch_all("SELECT * FROM notices ORDER BY posted_date DESC LIMIT 4");
} catch (Exception $e) {
    $featuredEvents = [];
    $recentNotices = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Wireframe 1: Hero banner + "Explore events" button -->
<section class="hero-section" aria-labelledby="hero-heading">
  <div class="container hero-grid">
    <div class="hero-content">
      <span class="badge badge-primary" style="margin-bottom:12px;">CHARUSAT • FTE Campus Portal</span>
      <h1 id="hero-heading">Discover Campus Life with <span class="gradient-text">StudentHub</span></h1>
      <p>
        Your all-in-one portal for university event registrations, official academic notices, student clubs, hackathons, and laboratory resources.
      </p>
      <div class="hero-buttons">
        <a href="events.php" class="btn btn-primary btn-lg">Explore Events →</a>
        <a href="about.php" class="btn btn-secondary btn-lg">Learn More</a>
      </div>
    </div>
    <div class="hero-img">
      <img src="../assets/images/hero_illustration.svg" alt="Students engaged in university campus activities" width="600" height="400">
    </div>
  </div>
</section>

<!-- Practical 4: Dynamic Interactive Image/Content Slider -->
<section class="container" aria-label="Campus Highlights Slider" style="margin-top:30px;">
  <div class="slider-container" id="heroSlider">
    <div class="slider-track">
      <div class="slide">
        <img src="../assets/images/event_techfest.svg" alt="Spoural TechFest" loading="lazy">
        <div class="slide-caption">
          <h3>Spoural 2026 - National Tech Festival</h3>
          <p>Compete in coding sprints, robotics battles, and web design hackathons with teams across the nation.</p>
        </div>
      </div>
      <div class="slide">
        <img src="../assets/images/event_hackathon.svg" alt="Web Dev Hackathon" loading="lazy">
        <div class="slide-caption">
          <h3>24-Hour Web Dev Frameworks Hackathon</h3>
          <p>Build accessible full-stack applications using modern HTML5, CSS Grid, and REST APIs.</p>
        </div>
      </div>
      <div class="slide">
        <img src="../assets/images/event_ai_seminar.svg" alt="AI Seminar" loading="lazy">
        <div class="slide-caption">
          <h3>Distinguished AI & Deep Learning Seminar</h3>
          <p>Learn state-of-the-art Generative AI and autonomous agent frameworks from industry leaders.</p>
        </div>
      </div>
    </div>

    <!-- Controls -->
    <button class="slider-nav slider-prev" aria-label="Previous slide">&#10094;</button>
    <button class="slider-nav slider-next" aria-label="Next slide">&#10095;</button>
    <div class="slider-dots" role="tablist" aria-label="Slide navigation"></div>
  </div>
</section>

<!-- Wireframe 1: Highlight Cards: events, notices, clubs -->
<section class="container" style="margin-top:20px;">
  <div class="section-header">
    <h2>Campus Life at a Glance</h2>
    <p>Everything you need for an enriching academic and extra-curricular journey at CHARUSAT.</p>
  </div>

  <div class="highlights-grid">
    <!-- Highlight 1: Events -->
    <article class="highlight-card">
      <div class="card-icon">📅</div>
      <h3>Upcoming Events</h3>
      <p>Browse campus workshops, tech fests, sports tournaments, and guest seminars. Reserve your seats with one click.</p>
      <a href="events.php" class="btn btn-outline">View All Events &rarr;</a>
    </article>

    <!-- Highlight 2: Notices -->
    <article class="highlight-card">
      <div class="card-icon">📢</div>
      <h3>Academic Notices</h3>
      <p>Stay up to date with urgent semester notices, exam timetables, practical lab submission schedules, and holidays.</p>
      <a href="#notices-section" class="btn btn-outline">Read Notices &rarr;</a>
    </article>

    <!-- Highlight 3: Clubs -->
    <article class="highlight-card">
      <div class="card-icon">👥</div>
      <h3>Student Clubs</h3>
      <p>Join Google Developer Student Club (GDSC), Robotics Club, Coding Ninjas, Photography Circle, and Cultural Wing.</p>
      <a href="about.php#clubs" class="btn btn-outline">Explore Clubs &rarr;</a>
    </article>
  </div>
</section>

<!-- Featured Events Grid Preview -->
<section class="container" style="margin-bottom:60px;">
  <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:28px; flex-wrap:wrap; gap:16px;">
    <div>
      <h2 style="font-size:1.8rem; font-weight:800; margin-bottom:6px;">Featured Campus Events</h2>
      <p style="color:var(--text-muted);">Trending activities scheduled across CHARUSAT departments.</p>
    </div>
    <a href="events.php" class="btn btn-secondary">View All Events ({<?= count($featuredEvents) ?>+}) &rarr;</a>
  </div>

  <div class="events-grid">
    <?php if (!empty($featuredEvents)): ?>
      <?php foreach ($featuredEvents as $ev): ?>
        <article class="event-card">
          <div class="event-card-poster">
            <img src="../assets/images/<?= htmlspecialchars($ev['poster_url']) ?>" alt="<?= htmlspecialchars($ev['title']) ?>" loading="lazy">
            <span class="event-badge"><?= htmlspecialchars($ev['category']) ?></span>
          </div>
          <div class="event-card-body">
            <div class="event-meta">
              <span>📅 <?= htmlspecialchars($ev['event_date']) ?></span>
              <span>📍 <?= htmlspecialchars($ev['venue']) ?></span>
            </div>
            <h3 class="event-title"><?= htmlspecialchars($ev['title']) ?></h3>
            <p class="event-desc"><?= htmlspecialchars($ev['description']) ?></p>
            <div class="event-card-footer">
              <span class="seats-info"><?= $ev['max_seats'] - $ev['registered_count'] ?> seats remaining</span>
              <a href="event_detail.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-primary">Details & Register</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="color:var(--text-muted);">No events found in database.</p>
    <?php endif; ?>
  </div>
</section>

<!-- Notices Section -->
<section class="container" id="notices-section" style="margin-bottom:70px;">
  <div class="table-card">
    <div class="table-header">
      <h3 style="font-size:1.25rem; font-weight:700;">📢 Official FTE Announcements & Notices</h3>
      <span class="badge badge-primary">Active Semester</span>
    </div>
    <div class="table-responsive">
      <table class="data-table" aria-label="Campus Notices">
        <thead>
          <tr>
            <th>Date</th>
            <th>Category</th>
            <th>Subject / Announcement</th>
            <th>Priority</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentNotices as $notice): ?>
            <tr>
              <td><strong><?= htmlspecialchars($notice['posted_date']) ?></strong></td>
              <td><span class="badge badge-neutral"><?= htmlspecialchars($notice['category']) ?></span></td>
              <td>
                <div style="font-weight:600;"><?= htmlspecialchars($notice['title']) ?></div>
                <div style="font-size:0.875rem; color:var(--text-muted);"><?= htmlspecialchars($notice['content']) ?></div>
              </td>
              <td>
                <span class="badge badge-<?= $notice['priority'] === 'urgent' ? 'danger' : 'success' ?>">
                  <?= htmlspecialchars($notice['priority']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
