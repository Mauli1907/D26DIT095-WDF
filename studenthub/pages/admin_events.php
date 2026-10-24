<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

require_admin();

$currentUser = current_user();
$errors = [];

// Practical 12: Event Poster Upload Helper with MIME & File Size validation
function handle_poster_upload(?array $file): array {
    if (!$file || empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'filename' => 'default_poster.svg'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error code: ' . $file['error']];
    }

    // 1. File size validation (Max 2MB = 2 * 1024 * 1024 bytes)
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size exceeds maximum allowed limit of 2MB.'];
    }

    // 2. MIME type & extension validation
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($mimeType, $allowedMimes, true) || !in_array($ext, $allowedExts, true)) {
        return ['success' => false, 'error' => 'Invalid file format. Only JPG, PNG, WEBP, and SVG are allowed.'];
    }

    // 3. Image renaming to prevent name collision & path traversal (Practical 12 Advanced)
    $safeName = 'poster_' . uniqid('', true) . '.' . $ext;
    $targetDir = __DIR__ . '/../assets/uploads/posters/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $targetPath = $targetDir . $safeName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Copy also into assets/images so relative paths remain simple
        @copy($targetPath, __DIR__ . '/../assets/images/' . $safeName);
        return ['success' => true, 'filename' => $safeName];
    } else {
        return ['success' => false, 'error' => 'Failed to save uploaded file to destination directory.'];
    }
}

// Handle Event Form Submissions
$action = $_POST['action'] ?? '';

// 1. ADD EVENT
if ($action === 'add_event') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF verification failed.');
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $date = trim($_POST['event_date'] ?? '');
        $time = trim($_POST['event_time'] ?? '09:00:00');
        $venue = trim($_POST['venue'] ?? '');
        $seats = (int)($_POST['max_seats'] ?? 100);
        $desc = trim($_POST['description'] ?? '');

        if (empty($title) || empty($category) || empty($date) || empty($venue)) {
            set_flash('error', 'Please fill all required event details.');
        } else {
            $uploadRes = handle_poster_upload($_FILES['poster'] ?? null);
            if (!$uploadRes['success']) {
                set_flash('error', $uploadRes['error']);
            } else {
                $posterFile = $uploadRes['filename'];
                db_query("INSERT INTO events (title, category, event_date, event_time, venue, description, poster_url, max_seats, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'upcoming')",
                    [$title, $category, $date, $time, $venue, $desc, $posterFile, $seats]);
                
                log_audit_action('EVENT_CREATE', "Admin created event '$title' with poster '$posterFile'", $currentUser['id'], 'admin');
                set_flash('success', "Event '$title' created successfully!");
            }
        }
    }
    header('Location: admin_events.php');
    exit;
}

// 2. EDIT EVENT
if ($action === 'edit_event') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF verification failed.');
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $date = trim($_POST['event_date'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $status = trim($_POST['status'] ?? 'upcoming');
        $desc = trim($_POST['description'] ?? '');

        if ($id > 0 && !empty($title)) {
            // Check if new poster uploaded
            if (!empty($_FILES['poster']['name'])) {
                $uploadRes = handle_poster_upload($_FILES['poster']);
                if ($uploadRes['success']) {
                    db_query("UPDATE events SET title = ?, category = ?, event_date = ?, venue = ?, status = ?, description = ?, poster_url = ? WHERE id = ?",
                        [$title, $category, $date, $venue, $status, $desc, $uploadRes['filename'], $id]);
                }
            } else {
                db_query("UPDATE events SET title = ?, category = ?, event_date = ?, venue = ?, status = ?, description = ? WHERE id = ?",
                    [$title, $category, $date, $venue, $status, $desc, $id]);
            }

            log_audit_action('EVENT_UPDATE', "Admin updated event ID: $id ($title)", $currentUser['id'], 'admin');
            set_flash('success', "Event '$title' updated successfully!");
        }
    }
    header('Location: admin_events.php');
    exit;
}

// 3. DELETE EVENT
if ($action === 'delete_event') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF verification failed.');
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $event = db_fetch_one("SELECT title FROM events WHERE id = ?", [$id]);
        if ($event) {
            db_query("DELETE FROM events WHERE id = ?", [$id]);
            log_audit_action('EVENT_DELETE', "Admin deleted event ID: $id ({$event['title']})", $currentUser['id'], 'admin');
            set_flash('warning', "Event ID #$id deleted.");
        }
    }
    header('Location: admin_events.php');
    exit;
}

// Category & Status filter (Practical 12 Intermediate)
$catFilter = trim($_GET['category'] ?? 'all');
$statusFilter = trim($_GET['status'] ?? 'all');

$where = "WHERE 1=1";
$params = [];
if ($catFilter !== 'all' && !empty($catFilter)) {
    $where .= " AND category = ?";
    $params[] = $catFilter;
}
if ($statusFilter !== 'all' && !empty($statusFilter)) {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}

$events = db_fetch_all("SELECT * FROM events $where ORDER BY event_date ASC", $params);
$categories = db_fetch_all("SELECT DISTINCT category FROM events ORDER BY category ASC");

$pageTitle = 'Manage Events (Practical 12) - StudentHub';
$activePage = 'admin';
$breadcrumbs = ['Admin Panel' => 'admin_dashboard.php', 'Events CRUD' => ''];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:20px; margin-bottom:60px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
      <span class="badge badge-primary">Practical 12: File Upload & Poster Validation</span>
      <h1 style="font-size:2rem; font-weight:800; margin-top:6px;">Event Management Module</h1>
      <p style="color:var(--text-muted);">Create and manage campus events with validated poster uploads (MIME type, size limits & renaming).</p>
    </div>
    <div>
      <button type="button" class="btn btn-primary" data-modal-target="addEventModal">+ Add New Event</button>
    </div>
  </div>

  <!-- Filter Bar (Practical 12 Intermediate: Category & status filter) -->
  <form method="GET" action="admin_events.php" class="filter-bar" style="grid-template-columns: 2fr 1fr 1fr auto;">
    <div class="filter-group">
      <label for="adminCatFilter">Filter Category</label>
      <select id="adminCatFilter" name="category" class="filter-select">
        <option value="all">All Categories</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= htmlspecialchars($c['category']) ?>" <?= $catFilter === $c['category'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['category']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="filter-group">
      <label for="adminStatusFilter">Filter Status</label>
      <select id="adminStatusFilter" name="status" class="filter-select">
        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
        <option value="upcoming" <?= $statusFilter === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
        <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
        <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
      </select>
    </div>

    <div></div>

    <div style="display:flex; gap:8px; align-self:flex-end;">
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="admin_events.php" class="btn btn-secondary">Reset</a>
    </div>
  </form>

  <!-- Events Data Table -->
  <div class="table-card">
    <div class="table-header">
      <div>
        <h3 style="font-size:1.2rem; font-weight:700;">Campus Events</h3>
        <p style="font-size:0.85rem; color:var(--text-muted);">Total Events: <?= count($events) ?></p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="data-table" aria-label="Events Table">
        <thead>
          <tr>
            <th>Poster</th>
            <th>Event Title & Venue</th>
            <th>Category</th>
            <th>Date & Time</th>
            <th>Registrations</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($events)): ?>
            <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">No events found.</td></tr>
          <?php else: ?>
            <?php foreach ($events as $ev): ?>
              <tr>
                <td style="width:70px;">
                  <img src="../assets/images/<?= htmlspecialchars($ev['poster_url']) ?>" alt="Poster" 
                       style="width:60px; height:38px; border-radius:4px; object-fit:cover;">
                </td>
                <td>
                  <div style="font-weight:700;"><?= htmlspecialchars($ev['title']) ?></div>
                  <small style="color:var(--text-muted);">📍 <?= htmlspecialchars($ev['venue']) ?></small>
                </td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($ev['category']) ?></span></td>
                <td>
                  <div><?= date('M d, Y', strtotime($ev['event_date'])) ?></div>
                  <small style="color:var(--text-muted);"><?= date('h:i A', strtotime($ev['event_time'])) ?></small>
                </td>
                <td><?= (int)$ev['registered_count'] ?> / <?= (int)$ev['max_seats'] ?></td>
                <td>
                  <span class="badge badge-<?= $ev['status'] === 'upcoming' ? 'success' : 'neutral' ?>">
                    <?= htmlspecialchars(ucfirst($ev['status'])) ?>
                  </span>
                </td>
                <td>
                  <div style="display:flex; gap:6px;">
                    <a href="event_detail.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-outline">Preview</a>
                    <button type="button" class="btn btn-sm btn-secondary" onclick='openEditEventModal(<?= json_encode($ev) ?>)'>Edit</button>
                    <form method="POST" action="admin_events.php" onsubmit="return confirm('Delete event <?= addslashes($ev['title']) ?>?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete_event">
                      <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Add Event with Poster Upload -->
<div class="modal-overlay" id="addEventModal" role="dialog" aria-modal="true" aria-labelledby="addEventTitle">
  <div class="modal-dialog" style="max-width:640px;">
    <div class="modal-header">
      <h3 id="addEventTitle">Create New Campus Event</h3>
      <button type="button" class="modal-close" data-close-modal>&times;</button>
    </div>
    <form method="POST" action="admin_events.php" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_event">

      <div class="form-group">
        <label for="event_title" class="form-label">Event Title <span class="required">*</span></label>
        <input type="text" id="event_title" name="title" class="form-control" placeholder="e.g., CodeSprint 2026" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="event_cat" class="form-label">Category <span class="required">*</span></label>
          <select id="event_cat" name="category" class="form-control" required>
            <option value="Technical">Technical</option>
            <option value="Hackathon">Hackathon</option>
            <option value="Workshop">Workshop</option>
            <option value="Seminar">Seminar</option>
            <option value="Cultural">Cultural</option>
            <option value="Sports">Sports</option>
            <option value="Career">Career</option>
          </select>
        </div>
        <div class="form-group">
          <label for="event_seats" class="form-label">Max Seats</label>
          <input type="number" id="event_seats" name="max_seats" class="form-control" value="100" min="10" max="1000">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="event_date" class="form-label">Event Date <span class="required">*</span></label>
          <input type="date" id="event_date" name="event_date" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="event_time" class="form-label">Event Time</label>
          <input type="time" id="event_time" name="event_time" class="form-control" value="10:00">
        </div>
      </div>

      <div class="form-group">
        <label for="event_venue" class="form-label">Venue / Room <span class="required">*</span></label>
        <input type="text" id="event_venue" name="venue" class="form-control" placeholder="e.g., FTE Auditorium Block B" required>
      </div>

      <!-- Poster Upload with File Validation (Practical 12) -->
      <div class="form-group" style="padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm);">
        <label for="event_poster" class="form-label">Upload Event Poster (Max 2MB - JPG, PNG, WEBP)</label>
        <input type="file" id="event_poster" name="poster" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
        <small style="color:var(--text-muted); display:block; margin-top:4px;">Files are automatically validated server-side and renamed safely.</small>
      </div>

      <div class="form-group">
        <label for="event_desc" class="form-label">Event Description</label>
        <textarea id="event_desc" name="description" class="form-control" rows="3" placeholder="Explain agenda, eligibility, prizes, and rules..."></textarea>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
        <button type="submit" class="btn btn-primary">Publish Event &rarr;</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit Event -->
<div class="modal-overlay" id="editEventModal" role="dialog" aria-modal="true" aria-labelledby="editEventTitle">
  <div class="modal-dialog" style="max-width:640px;">
    <div class="modal-header">
      <h3 id="editEventTitle">Edit Event Details</h3>
      <button type="button" class="modal-close" data-close-modal>&times;</button>
    </div>
    <form method="POST" action="admin_events.php" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="edit_event">
      <input type="hidden" id="edit_ev_id" name="id" value="">

      <div class="form-group">
        <label for="edit_ev_title" class="form-label">Title</label>
        <input type="text" id="edit_ev_title" name="title" class="form-control" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="edit_ev_category" class="form-label">Category</label>
          <select id="edit_ev_category" name="category" class="form-control">
            <option value="Technical">Technical</option>
            <option value="Hackathon">Hackathon</option>
            <option value="Workshop">Workshop</option>
            <option value="Seminar">Seminar</option>
            <option value="Cultural">Cultural</option>
            <option value="Sports">Sports</option>
            <option value="Career">Career</option>
          </select>
        </div>
        <div class="form-group">
          <label for="edit_ev_status" class="form-label">Status</label>
          <select id="edit_ev_status" name="status" class="form-control">
            <option value="upcoming">Upcoming</option>
            <option value="ongoing">Ongoing</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="edit_ev_date" class="form-label">Date</label>
          <input type="date" id="edit_ev_date" name="event_date" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_ev_venue" class="form-label">Venue</label>
          <input type="text" id="edit_ev_venue" name="venue" class="form-control" required>
        </div>
      </div>

      <div class="form-group">
        <label for="edit_ev_poster" class="form-label">Replace Poster (Optional)</label>
        <input type="file" id="edit_ev_poster" name="poster" class="form-control" accept="image/*">
      </div>

      <div class="form-group">
        <label for="edit_ev_desc" class="form-label">Description</label>
        <textarea id="edit_ev_desc" name="description" class="form-control" rows="3"></textarea>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
        <button type="submit" class="btn btn-primary">Update Event</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditEventModal(ev) {
  document.getElementById('edit_ev_id').value = ev.id;
  document.getElementById('edit_ev_title').value = ev.title;
  document.getElementById('edit_ev_category').value = ev.category;
  document.getElementById('edit_ev_status').value = ev.status;
  document.getElementById('edit_ev_date').value = ev.event_date;
  document.getElementById('edit_ev_venue').value = ev.venue;
  document.getElementById('edit_ev_desc').value = ev.description;

  const modal = document.getElementById('editEventModal');
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
