<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

$eventId = (int)($_GET['id'] ?? 0);
if ($eventId <= 0) {
    set_flash('error', 'Invalid event selected.');
    header('Location: events.php');
    exit;
}

$event = db_fetch_one("SELECT * FROM events WHERE id = ?", [$eventId]);
if (!$event) {
    set_flash('error', 'Event not found.');
    header('Location: events.php');
    exit;
}

$pageTitle = htmlspecialchars($event['title']) . ' - StudentHub';
$activePage = 'events';
$breadcrumbs = [
    'Events' => 'events.php',
    $event['title'] => ''
];

$currentUser = current_user();
$isRegistered = false;

if ($currentUser) {
    $existing = db_fetch_one("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?", [$currentUser['id'], $eventId]);
    if ($existing) {
        $isRegistered = true;
    }
}

// Handle Form Registration POST (Wireframe 4 Register button)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid security token (CSRF). Please try again.');
        header("Location: event_detail.php?id=$eventId");
        exit;
    }

    if (!$currentUser) {
        set_flash('warning', 'Please log in to your student account to register for this event.');
        header("Location: login.php?redirect=" . urlencode("event_detail.php?id=$eventId"));
        exit;
    }

    if ($isRegistered) {
        set_flash('info', 'You are already registered for this event.');
        header("Location: event_detail.php?id=$eventId");
        exit;
    }

    if ($event['registered_count'] >= $event['max_seats']) {
        set_flash('error', 'Sorry, registration is closed. All seats are filled.');
        header("Location: event_detail.php?id=$eventId");
        exit;
    }

    try {
        // Execute registration insert
        db_query("INSERT INTO registrations (user_id, event_id, student_name, student_email, status) VALUES (?, ?, ?, ?, 'registered')",
            [$currentUser['id'], $eventId, $currentUser['name'], $currentUser['email']]);

        // Increment count
        db_query("UPDATE events SET registered_count = registered_count + 1 WHERE id = ?", [$eventId]);

        // Log audit (Practical 14)
        log_audit_action('EVENT_REGISTRATION', "Student {$currentUser['name']} registered for {$event['title']} (Event ID: $eventId)", $currentUser['id'], $currentUser['role']);

        set_flash('success', '🎉 Congratulations! You have successfully registered for ' . $event['title']);
        header("Location: student_dashboard.php");
        exit;
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        set_flash('error', 'Registration failed: ' . $e->getMessage());
        header("Location: event_detail.php?id=$eventId");
        exit;
    }
}

$seatsLeft = max(0, $event['max_seats'] - $event['registered_count']);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:20px;">
  <!-- Wireframe 4: Event details layout -->
  <div class="event-detail-wrapper">
    <!-- Main content: Poster + title + description -->
    <article class="event-detail-main">
      <div class="event-detail-poster">
        <img src="../assets/images/<?= htmlspecialchars($event['poster_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" width="800" height="420">
      </div>
      <div class="event-detail-body">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
          <span class="badge badge-primary"><?= htmlspecialchars($event['category']) ?></span>
          <span class="badge badge-<?= $event['status'] === 'upcoming' ? 'success' : 'neutral' ?>">Status: <?= htmlspecialchars($event['status']) ?></span>
        </div>

        <h1 style="font-size:2.2rem; font-weight:800; margin-bottom:18px; line-height:1.2;"><?= htmlspecialchars($event['title']) ?></h1>

        <div style="display:flex; flex-wrap:wrap; gap:20px; padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); margin-bottom:28px;">
          <div><strong>📅 Date:</strong> <?= date('l, F j, Y', strtotime($event['event_date'])) ?></div>
          <div><strong>⏰ Time:</strong> <?= date('h:i A', strtotime($event['event_time'])) ?></div>
          <div><strong>📍 Venue:</strong> <?= htmlspecialchars($event['venue']) ?></div>
        </div>

        <h3 style="font-size:1.35rem; margin-bottom:12px;">About the Event</h3>
        <div style="line-height:1.8; color:var(--text-main); font-size:1.05rem;">
          <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
        </div>

        <div style="margin-top:36px; padding:24px; background:var(--primary-light); border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
          <h4 style="color:var(--primary-dark); margin-bottom:6px;">Participation Guidelines</h4>
          <ul style="padding-left:20px; color:var(--text-main); font-size:0.95rem; line-height:1.7;">
            <li>All CHARUSAT FTE students must carry their official University ID cards.</li>
            <li>Attendance will be marked digitally upon entering the venue.</li>
            <li>E-Certificates will be issued to registered attendees through the StudentHub dashboard.</li>
          </ul>
        </div>
      </div>
    </article>

    <!-- Sidebar: Event specs & Register button -->
    <aside class="event-detail-sidebar" aria-label="Registration specifications">
      <div class="info-card">
        <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:14px;">Event Details</h3>
        <ul class="info-list">
          <li><span class="label">Total Capacity:</span> <span class="val"><?= (int)$event['max_seats'] ?> Students</span></li>
          <li><span class="label">Confirmed Seats:</span> <span class="val"><?= (int)$event['registered_count'] ?></span></li>
          <li><span class="label">Seats Remaining:</span> <span class="val" style="color:var(--primary);"><?= $seatsLeft ?></span></li>
          <li><span class="label">Registration Fee:</span> <span class="val" style="color:var(--success);">Free (Campus)</span></li>
          <li><span class="label">Eligibility:</span> <span class="val">All FTE Departments</span></li>
        </ul>

        <!-- Wireframe 4: Register button -->
        <?php if ($isRegistered): ?>
          <div style="padding:14px; background:#d1fae5; border-radius:var(--radius-sm); text-align:center; color:#065f46; font-weight:700; margin-top:20px;">
            ✅ You are registered for this event!
          </div>
          <a href="student_dashboard.php" class="btn btn-secondary btn-block" style="margin-top:12px;">View in My Dashboard</a>
        <?php elseif ($seatsLeft <= 0): ?>
          <button class="btn btn-secondary btn-block" disabled style="margin-top:20px;">Seats Full</button>
        <?php else: ?>
          <form method="POST" action="event_detail.php?id=<?= $eventId ?>" style="margin-top:20px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="register">
            <?php if ($currentUser): ?>
              <button type="submit" class="btn btn-primary btn-lg btn-block">Register for Event &rarr;</button>
            <?php else: ?>
              <a href="login.php?redirect=<?= urlencode("event_detail.php?id=$eventId") ?>" class="btn btn-primary btn-lg btn-block">Log in to Register &rarr;</a>
              <small style="display:block; text-align:center; color:var(--text-muted); margin-top:8px;">Student account required</small>
            <?php endif; ?>
          </form>
        <?php endif; ?>
      </div>

      <div class="info-card">
        <h4 style="font-size:1.1rem; margin-bottom:10px;">Need Help?</h4>
        <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:14px;">
          Have questions about this competition or workshop? Reach out to student coordinators.
        </p>
        <a href="contact.php?subject=<?= urlencode("Query regarding " . $event['title']) ?>" class="btn btn-sm btn-outline btn-block">Contact Coordinator</a>
      </div>
    </aside>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
