<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$pageTitle = 'Stored Form Submissions (Practical 7) - StudentHub';
$activePage = 'contact';
$breadcrumbs = ['Contact' => 'contact.php', 'Submissions (CSV/JSON)' => ''];

$csvFile = __DIR__ . '/../data/submissions.csv';
$jsonFile = __DIR__ . '/../data/submissions.json';

// Read CSV rows
$csvRows = [];
if (file_exists($csvFile)) {
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        $header = fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 5) {
                $csvRows[] = $data;
            }
        }
        fclose($handle);
    }
}

// Read JSON records
$jsonRecords = [];
if (file_exists($jsonFile)) {
    $raw = file_get_contents($jsonFile);
    $jsonRecords = json_decode($raw, true) ?? [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:20px; margin-bottom:60px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
      <span class="badge badge-primary">Practical 7 Intermediate Extension</span>
      <h1 style="font-size:2rem; font-weight:800; margin-top:6px;">Server-Side Stored Submissions</h1>
      <p style="color:var(--text-muted);">Displays form submissions validated on the backend and saved directly to CSV and JSON flat files.</p>
    </div>
    <div>
      <a href="contact.php" class="btn btn-primary">+ Submit Another Feedback</a>
    </div>
  </div>

  <!-- CSV Section -->
  <div class="table-card" style="margin-bottom:40px;">
    <div class="table-header">
      <div>
        <h3 style="font-size:1.2rem; font-weight:700;">📄 Stored in <code>data/submissions.csv</code></h3>
        <p style="font-size:0.85rem; color:var(--text-muted);">Total Rows: <?= count($csvRows) ?></p>
      </div>
      <a href="../data/submissions.csv" download class="btn btn-sm btn-outline">⬇️ Download CSV File</a>
    </div>
    <div class="table-responsive">
      <table class="data-table" aria-label="CSV Submissions">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Message</th>
            <th>Timestamp</th>
            <th>IP Address</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($csvRows)): ?>
            <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">No CSV records found yet.</td></tr>
          <?php else: ?>
            <?php foreach (array_reverse($csvRows) as $row): ?>
              <tr>
                <td><strong>#<?= htmlspecialchars($row[0] ?? '') ?></strong></td>
                <td><?= htmlspecialchars($row[1] ?? '') ?></td>
                <td><?= htmlspecialchars($row[2] ?? '') ?></td>
                <td><strong><?= htmlspecialchars($row[3] ?? '') ?></strong></td>
                <td><?= htmlspecialchars($row[4] ?? '') ?></td>
                <td><?= htmlspecialchars($row[5] ?? '') ?></td>
                <td><code><?= htmlspecialchars($row[6] ?? '') ?></code></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- JSON Section -->
  <div class="table-card">
    <div class="table-header">
      <div>
        <h3 style="font-size:1.2rem; font-weight:700;">📦 Stored in <code>data/submissions.json</code></h3>
        <p style="font-size:0.85rem; color:var(--text-muted);">Total JSON Objects: <?= count($jsonRecords) ?></p>
      </div>
      <a href="../data/submissions.json" download class="btn btn-sm btn-outline">⬇️ Download JSON File</a>
    </div>
    <div class="table-responsive">
      <table class="data-table" aria-label="JSON Submissions">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Message</th>
            <th>Submitted At</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($jsonRecords)): ?>
            <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">No JSON records found yet.</td></tr>
          <?php else: ?>
            <?php foreach (array_reverse($jsonRecords) as $rec): ?>
              <tr>
                <td><strong>#<?= htmlspecialchars($rec['id'] ?? '') ?></strong></td>
                <td><?= htmlspecialchars($rec['name'] ?? '') ?></td>
                <td><?= htmlspecialchars($rec['email'] ?? '') ?></td>
                <td><strong><?= htmlspecialchars($rec['subject'] ?? '') ?></strong></td>
                <td><?= htmlspecialchars($rec['message'] ?? '') ?></td>
                <td><?= htmlspecialchars($rec['submission_time'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
