<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

// Auth Guard: Require student
require_student();

$currentUser = current_user();
$userId = $currentUser['id'];

// Handle cancel registration action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_registration') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $regId = (int)($_POST['reg_id'] ?? 0);
        $reg = db_fetch_one("SELECT * FROM registrations WHERE id = ? AND user_id = ?", [$regId, $userId]);
        if ($reg) {
            // Delete registration
            db_query("DELETE FROM registrations WHERE id = ?", [$regId]);
            // Decrement registered count in events table
            db_query("UPDATE events SET registered_count = GREATEST(0, registered_count - 1) WHERE id = ?", [$reg['event_id']]);
            log_audit_action('CANCEL_REGISTRATION', "Student {$currentUser['name']} cancelled registration ID: $regId", $userId, 'student');
            set_flash('success', 'Registration cancelled successfully.');
        }
    }
    header('Location: student_dashboard.php');
    exit;
}

// Fetch student profile details
$studentProfile = db_fetch_one("SELECT * FROM students WHERE user_id = ?", [$userId]);

// Fetch registered events with event details (Practical 8 Stored Procedure or JOIN)
$registeredEvents = db_fetch_all("
    SELECT r.id AS reg_id, r.registration_date, r.status AS reg_status,
           e.id AS event_id, e.title, e.category, e.event_date, e.event_time, e.venue, e.poster_url
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.user_id = ?
    ORDER BY r.registration_date DESC
", [$userId]);

// Fetch recent notices
$notices = db_fetch_all("SELECT * FROM notices ORDER BY posted_date DESC LIMIT 5");

$pageTitle = 'Student Dashboard - StudentHub | CHARUSAT FTE';
$activePage = 'dashboard';
$breadcrumbs = ['Dashboard' => ''];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <!-- Wireframe 7: Student dashboard layout -->
  <div class="dashboard-grid">
    <!-- Sidebar: Logo/avatar + Quick links: my events, notices, profile -->
    <aside class="dashboard-sidebar">
      <div class="user-profile-badge">
        <img src="../assets/images/<?= htmlspecialchars($studentProfile['profile_pic'] ?? 'default_avatar.svg') ?>" 
             alt="<?= htmlspecialchars($currentUser['name']) ?>" class="profile-avatar-lg">
        <h3 style="font-size:1.15rem; margin-bottom:4px;"><?= htmlspecialchars($currentUser['name']) ?></h3>
        <p style="font-size:0.85rem; color:var(--text-muted);"><?= htmlspecialchars($studentProfile['course'] ?? 'Student') ?> &bull; <?= htmlspecialchars($studentProfile['year_of_study'] ?? 'FTE') ?></p>
        <span class="badge badge-success" style="margin-top:6px;">Active Student</span>
      </div>

      <!-- Quick links: my events, notices, profile -->
      <nav aria-label="Student Navigation">
        <ul class="side-menu">
          <li><a href="student_dashboard.php" class="active">📅 My Registered Events</a></li>
          <li><a href="#recent-notices">📢 Official Notices</a></li>
          <li><a href="profile.php">👤 Edit Profile</a></li>
          <li><a href="events.php">🔍 Browse New Events</a></li>
          <li><a href="faq.php">❓ Campus Help & FAQ</a></li>
          <li><a href="logout.php" style="color:var(--danger);">🚪 Logout</a></li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <section>
      <!-- Stats Overview -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon" style="background:#e0e7ff; color:#4338ca;">🎟️</div>
          <div>
            <div class="stat-val"><?= count($registeredEvents) ?></div>
            <div class="stat-label">Events Registered</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:#d1fae5; color:#065f46;">🏅</div>
          <div>
            <div class="stat-val">3</div>
            <div class="stat-label">Certificates Earned</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:#fef3c7; color:#92400e;">📢</div>
          <div>
            <div class="stat-val"><?= count($notices) ?></div>
            <div class="stat-label">Unread Notices</div>
          </div>
        </div>
      </div>

      <!-- Wireframe 7: List: registered events + status -->
      <div class="table-card">
        <div class="table-header">
          <div>
            <h3 style="font-size:1.25rem; font-weight:700;">My Registered Events</h3>
            <p style="font-size:0.875rem; color:var(--text-muted);">List of competitions, seminars, and hackathons you have signed up for.</p>
          </div>
          <a href="events.php" class="btn btn-sm btn-primary">+ Register for More</a>
        </div>

        <div class="table-responsive">
          <?php if (empty($registeredEvents)): ?>
            <div style="text-align:center; padding:40px; color:var(--text-muted);">
              <p style="font-size:2rem; margin-bottom:8px;">📅</p>
              <h4>No registered events yet</h4>
              <p>Explore upcoming hackathons and tech festivals on campus!</p>
              <a href="events.php" class="btn btn-sm btn-outline" style="margin-top:12px;">Explore Events &rarr;</a>
            </div>
          <?php else: ?>
            <table class="data-table" aria-label="Registered Events Table">
              <thead>
                <tr>
                  <th>Event Title</th>
                  <th>Category</th>
                  <th>Date & Time</th>
                  <th>Venue</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($registeredEvents as $reg): ?>
                  <tr>
                    <td>
                      <div style="font-weight:700;"><?= htmlspecialchars($reg['title']) ?></div>
                      <small style="color:var(--text-muted);">Reg Date: <?= date('M d, Y', strtotime($reg['registration_date'])) ?></small>
                    </td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars($reg['category']) ?></span></td>
                    <td>
                      <div><?= date('M d, Y', strtotime($reg['event_date'])) ?></div>
                      <small style="color:var(--text-muted);"><?= date('h:i A', strtotime($reg['event_time'])) ?></small>
                    </td>
                    <td><?= htmlspecialchars($reg['venue']) ?></td>
                    <td>
                      <span class="badge badge-<?= $reg['reg_status'] === 'attended' ? 'primary' : 'success' ?>">
                        <?= htmlspecialchars(ucfirst($reg['reg_status'])) ?>
                      </span>
                    </td>
                    <td>
                      <div style="display:flex; gap:8px;">
                        <a href="event_detail.php?id=<?= $reg['event_id'] ?>" class="btn btn-sm btn-secondary">View</a>
                        <form method="POST" action="student_dashboard.php" onsubmit="return confirm('Are you sure you want to cancel this event registration?');">
                          <?= csrf_field() ?>
                          <input type="hidden" name="action" value="cancel_registration">
                          <input type="hidden" name="reg_id" value="<?= $reg['reg_id'] ?>">
                          <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      <!-- Wireframe 7: Recent notices/announcements -->
      <div class="table-card" id="recent-notices">
        <div class="table-header">
          <h3 style="font-size:1.25rem; font-weight:700;">📢 Campus Announcements</h3>
          <span class="badge badge-neutral">Latest Updates</span>
        </div>
        <div class="table-responsive">
          <table class="data-table" aria-label="Campus Notices">
            <thead>
              <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Subject</th>
                <th>Priority</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($notices as $nt): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($nt['posted_date']) ?></strong></td>
                  <td><span class="badge badge-neutral"><?= htmlspecialchars($nt['category']) ?></span></td>
                  <td>
                    <div style="font-weight:600;"><?= htmlspecialchars($nt['title']) ?></div>
                    <div style="font-size:0.875rem; color:var(--text-muted);"><?= htmlspecialchars($nt['content']) ?></div>
                  </td>
                  <td>
                    <span class="badge badge-<?= $nt['priority'] === 'urgent' ? 'danger' : 'success' ?>">
                      <?= htmlspecialchars($nt['priority']) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
