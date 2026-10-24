<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

// Auth Guard: Admin access only (Practical 10 & 14)
require_admin();

$currentUser = current_user();

// Handle active/inactive user status toggle (Practical 14 Intermediate Extension)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_user_status') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $user = db_fetch_one("SELECT id, status, full_name FROM users WHERE id = ?", [$targetUserId]);
        if ($user) {
            $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
            db_query("UPDATE users SET status = ? WHERE id = ?", [$newStatus, $targetUserId]);
            log_audit_action('USER_STATUS_TOGGLE', "Admin changed status of {$user['full_name']} (ID: $targetUserId) to $newStatus", $currentUser['id'], 'admin');
            set_flash('success', "Status for {$user['full_name']} updated to '$newStatus'.");
        }
    }
    header('Location: admin_dashboard.php');
    exit;
}

// Practical 14: Dynamic Aggregation Queries from Database
$totalStudents = (int)(db_fetch_one("SELECT COUNT(*) as count FROM students WHERE status != 'deleted'")['count'] ?? 0);
$totalEvents = (int)(db_fetch_one("SELECT COUNT(*) as count FROM events")['count'] ?? 0);
$totalRegistrations = (int)(db_fetch_one("SELECT COUNT(*) as count FROM registrations")['count'] ?? 0);
$newToday = (int)(db_fetch_one("SELECT COUNT(*) as count FROM registrations WHERE DATE(registration_date) = CURDATE()")['count'] ?? 0);

// Recent registrations with student & event details
$recentActivity = db_fetch_all("
    SELECT r.id, r.student_name, r.student_email, r.registration_date, r.status,
           e.title AS event_title, e.category, e.id AS event_id
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    ORDER BY r.registration_date DESC
    LIMIT 8
");

// Audit Logs preview
$recentAudits = db_fetch_all("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 5");

$pageTitle = 'Admin Dashboard - StudentHub | CHARUSAT FTE';
$activePage = 'admin';
$breadcrumbs = ['Admin Panel' => ''];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <!-- Wireframe 10: Admin dashboard layout -->
  <div class="dashboard-grid">
    <!-- Wireframe 10 Sidebar: Students, Events, Notices, Audit logs -->
    <aside class="dashboard-sidebar">
      <div class="user-profile-badge">
        <div style="font-size:2.8rem; line-height:1; margin-bottom:8px;">🛡️</div>
        <h3 style="font-size:1.15rem; margin-bottom:2px;">Admin Console</h3>
        <p style="font-size:0.85rem; color:var(--text-muted);"><?= htmlspecialchars($currentUser['email']) ?></p>
        <span class="badge badge-warning" style="margin-top:6px;">Administrator</span>
      </div>

      <nav aria-label="Admin Navigation">
        <ul class="side-menu">
          <li><a href="admin_dashboard.php" class="active">📊 Overview & Activity</a></li>
          <li><a href="admin_students.php">👥 Manage Students (Prac 11)</a></li>
          <li><a href="admin_events.php">📅 Manage Events (Prac 12)</a></li>
          <li><a href="ajax_demo.php">⚡ Fetch API Playground (Prac 13)</a></li>
          <li><a href="admin_audit.php">📜 Audit Logs (Prac 14)</a></li>
          <li><a href="submissions_view.php">📁 Form Submissions (Prac 7)</a></li>
          <li><a href="../practicals/index.php">🧪 Practicals Lab Guide</a></li>
          <li><a href="logout.php" style="color:var(--danger);">🚪 Logout</a></li>
        </ul>
      </nav>
    </aside>

    <!-- Main Admin Content Area -->
    <section>
      <!-- Wireframe 10 Top Cards: Students: 240, Events: 18, New: 5 -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon" style="background:#e0e7ff; color:#4338ca;">👨‍🎓</div>
          <div>
            <div class="stat-val"><?= $totalStudents ?></div>
            <div class="stat-label">Total Students</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:#fef3c7; color:#92400e;">📅</div>
          <div>
            <div class="stat-val"><?= $totalEvents ?></div>
            <div class="stat-label">Active Events</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:#d1fae5; color:#065f46;">🎟️</div>
          <div>
            <div class="stat-val"><?= $totalRegistrations ?></div>
            <div class="stat-label">Total Registrations</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:#fee2e2; color:#991b1b;">⚡</div>
          <div>
            <div class="stat-val"><?= $newToday ?></div>
            <div class="stat-label">New Registrations Today</div>
          </div>
        </div>
      </div>

      <!-- Practical 14 Advanced: Analytics Chart using Canvas -->
      <div class="table-card" style="margin-bottom:30px;">
        <div class="table-header">
          <div>
            <h3 style="font-size:1.15rem; font-weight:700;">📈 Event Registrations by Category</h3>
            <p style="font-size:0.85rem; color:var(--text-muted);">Practical 14 Advanced Extension: Visual Analytics</p>
          </div>
        </div>
        <div style="padding:24px; text-align:center;">
          <canvas id="categoryChart" width="700" height="200" style="max-width:100%; height:auto;" aria-label="Event category bar chart"></canvas>
        </div>
      </div>

      <!-- Wireframe 10: Table: recent registrations / activity + Manage buttons -->
      <div class="table-card">
        <div class="table-header">
          <div>
            <h3 style="font-size:1.25rem; font-weight:700;">Recent Activity & Registrations</h3>
            <p style="font-size:0.875rem; color:var(--text-muted);">Real-time stream of student event registrations.</p>
          </div>
          <!-- Wireframe 10 Manage buttons: add / edit / delete -->
          <div style="display:flex; gap:8px;">
            <a href="admin_events.php#add-event" class="btn btn-sm btn-primary">+ Add New Event</a>
            <a href="admin_students.php#add-student" class="btn btn-sm btn-secondary">+ Add Student</a>
          </div>
        </div>

        <div class="table-responsive">
          <table class="data-table" aria-label="Recent registrations">
            <thead>
              <tr>
                <th>Registration ID</th>
                <th>Student</th>
                <th>Event Title</th>
                <th>Category</th>
                <th>Registered At</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentActivity)): ?>
                <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">No activity recorded yet.</td></tr>
              <?php else: ?>
                <?php foreach ($recentActivity as $act): ?>
                  <tr>
                    <td><strong>#<?= $act['id'] ?></strong></td>
                    <td>
                      <div style="font-weight:700;"><?= htmlspecialchars($act['student_name']) ?></div>
                      <small style="color:var(--text-muted);"><?= htmlspecialchars($act['student_email']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($act['event_title']) ?></td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars($act['category']) ?></span></td>
                    <td><?= date('M d, Y h:i A', strtotime($act['registration_date'])) ?></td>
                    <td>
                      <span class="badge badge-<?= $act['status'] === 'attended' ? 'primary' : 'success' ?>">
                        <?= htmlspecialchars(ucfirst($act['status'])) ?>
                      </span>
                    </td>
                    <td>
                      <a href="event_detail.php?id=<?= $act['event_id'] ?>" class="btn btn-sm btn-outline">View Event</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Practical 14: Audit Logs Preview -->
      <div class="table-card">
        <div class="table-header">
          <div>
            <h3 style="font-size:1.25rem; font-weight:700;">📜 Security & Audit Traceability</h3>
            <p style="font-size:0.875rem; color:var(--text-muted);">Recorded user logins, creations, updates, and timeouts (Practical 14).</p>
          </div>
          <a href="admin_audit.php" class="btn btn-sm btn-outline">View Full Audit Trail &rarr;</a>
        </div>
        <div class="table-responsive">
          <table class="data-table" aria-label="Audit Logs Preview">
            <thead>
              <tr>
                <th>Timestamp</th>
                <th>Action</th>
                <th>Role</th>
                <th>Details</th>
                <th>IP Address</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentAudits as $log): ?>
                <tr>
                  <td><?= htmlspecialchars($log['created_at']) ?></td>
                  <td><span class="badge badge-neutral"><code><?= htmlspecialchars($log['action']) ?></code></span></td>
                  <td><?= htmlspecialchars($log['user_role']) ?></td>
                  <td><?= htmlspecialchars($log['details']) ?></td>
                  <td><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>

<script>
// Practical 14 Advanced: Draw Canvas Analytics Bar Chart
document.addEventListener('DOMContentLoaded', () => {
  const canvas = document.getElementById('categoryChart');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  const categories = [
    { label: 'Technical', count: 5, color: '#4f46e5' },
    { label: 'Hackathon', count: 3, color: '#7c3aed' },
    { label: 'Seminar', count: 4, color: '#0284c7' },
    { label: 'Workshop', count: 4, color: '#059669' },
    { label: 'Cultural', count: 3, color: '#e11d48' },
    { label: 'Career', count: 2, color: '#d97706' }
  ];

  const maxVal = 6;
  const barWidth = 46;
  const gap = 55;
  const startX = 60;
  const chartHeight = 130;
  const baseY = 160;

  // Draw grid lines
  ctx.strokeStyle = '#e2e8f0';
  ctx.lineWidth = 1;
  for (let i = 0; i <= maxVal; i += 2) {
    const y = baseY - (i / maxVal) * chartHeight;
    ctx.beginPath();
    ctx.moveTo(startX - 10, y);
    ctx.lineTo(startX + categories.length * (barWidth + gap), y);
    ctx.stroke();

    ctx.fillStyle = '#64748b';
    ctx.font = '12px Segoe UI, sans-serif';
    ctx.fillText(i.toString(), startX - 30, y + 4);
  }

  // Draw bars
  categories.forEach((cat, index) => {
    const x = startX + index * (barWidth + gap);
    const barH = (cat.count / maxVal) * chartHeight;
    const y = baseY - barH;

    ctx.fillStyle = cat.color;
    ctx.beginPath();
    ctx.roundRect ? ctx.roundRect(x, y, barWidth, barH, [4, 4, 0, 0]) : ctx.fillRect(x, y, barWidth, barH);
    ctx.fill();

    // Value on top
    ctx.fillStyle = '#0f172a';
    ctx.font = 'bold 12px Segoe UI, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(cat.count.toString(), x + barWidth / 2, y - 6);

    // Label below
    ctx.fillStyle = '#475569';
    ctx.font = '11px Segoe UI, sans-serif';
    ctx.fillText(cat.label, x + barWidth / 2, baseY + 18);
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
