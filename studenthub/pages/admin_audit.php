<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();

$actionFilter = trim($_GET['action'] ?? 'all');
$search = trim($_GET['search'] ?? '');

$where = "WHERE 1=1";
$params = [];

if ($actionFilter !== 'all' && !empty($actionFilter)) {
    $where .= " AND action = ?";
    $params[] = $actionFilter;
}

if (!empty($search)) {
    $where .= " AND (details LIKE ? OR ip_address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$logs = db_fetch_all("SELECT * FROM audit_logs $where ORDER BY created_at DESC LIMIT 100", $params);
$distinctActions = db_fetch_all("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC");

$pageTitle = 'Security Audit Logs (Practical 14) - StudentHub';
$activePage = 'admin';
$breadcrumbs = ['Admin Panel' => 'admin_dashboard.php', 'Audit Logs' => ''];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:20px; margin-bottom:60px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
      <span class="badge badge-primary">Practical 14: Traceability & RBAC Audit Logging</span>
      <h1 style="font-size:2rem; font-weight:800; margin-top:6px;">Security Audit Logs</h1>
      <p style="color:var(--text-muted);">Immutable audit trail tracking logins, event creations, registrations, updates, and timeouts.</p>
    </div>
    <div>
      <a href="admin_dashboard.php" class="btn btn-secondary">&larr; Return to Dashboard</a>
    </div>
  </div>

  <!-- Filter -->
  <form method="GET" action="admin_audit.php" class="filter-bar" style="grid-template-columns: 2fr 1.5fr auto;">
    <div class="filter-group">
      <label for="search">Search Details / IP</label>
      <input type="text" id="search" name="search" class="filter-input" placeholder="Search by description or IP..." value="<?= htmlspecialchars($search) ?>">
    </div>

    <div class="filter-group">
      <label for="action">Action Type</label>
      <select id="action" name="action" class="filter-select">
        <option value="all">All Recorded Actions</option>
        <?php foreach ($distinctActions as $act): ?>
          <option value="<?= htmlspecialchars($act['action']) ?>" <?= $actionFilter === $act['action'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($act['action']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="display:flex; gap:8px; align-self:flex-end;">
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="admin_audit.php" class="btn btn-secondary">Reset</a>
    </div>
  </form>

  <!-- Audit Table -->
  <div class="table-card">
    <div class="table-header">
      <div>
        <h3 style="font-size:1.2rem; font-weight:700;">Audit Trail Entries</h3>
        <p style="font-size:0.85rem; color:var(--text-muted);">Showing <?= count($logs) ?> logged security events</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="data-table" aria-label="Audit Logs Table">
        <thead>
          <tr>
            <th>Log ID</th>
            <th>Timestamp</th>
            <th>Action</th>
            <th>Role</th>
            <th>Details & Parameters</th>
            <th>Client IP</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">No logs match filter.</td></tr>
          <?php else: ?>
            <?php foreach ($logs as $l): ?>
              <tr>
                <td><strong>#<?= $l['id'] ?></strong></td>
                <td><?= date('Y-m-d H:i:s', strtotime($l['created_at'])) ?></td>
                <td><span class="badge badge-primary"><code><?= htmlspecialchars($l['action']) ?></code></span></td>
                <td><span class="badge badge-neutral"><?= htmlspecialchars($l['user_role'] ?? 'guest') ?></span></td>
                <td><?= htmlspecialchars($l['details']) ?></td>
                <td><code><?= htmlspecialchars($l['ip_address']) ?></code></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
