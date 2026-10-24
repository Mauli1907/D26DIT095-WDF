<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$pageTitle = 'Practical 13: Asynchronous Fetch API CRUD - StudentHub';
$activePage = 'practicals';
$breadcrumbs = ['Practicals Hub' => '../practicals/index.php', 'Practical 13: Fetch API CRUD' => ''];
$extraScript = '../assets/js/ajax_crud.js';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:20px; margin-bottom:70px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:14px;">
    <div>
      <span class="badge badge-primary">Practical 13: Modern Asynchronous Web App</span>
      <h1 style="font-size:2rem; font-weight:800; margin-top:6px;">Fetch API & JSON REST CRUD Playground</h1>
      <p style="color:var(--text-muted);">
        All operations (List, Add, Update, Delete, Search) happen asynchronously via JavaScript <code>fetch()</code> without any full page reload.
      </p>
    </div>
    <div style="display:flex; gap:10px;">
      <button type="button" class="btn btn-primary" data-modal-target="ajaxAddModal">+ Add Event via Fetch</button>
      <button type="button" class="btn btn-outline" onclick="fetchAndRenderEvents()">🔄 Reload Table</button>
    </div>
  </div>

  <!-- Real-time Filter Bar with Debounce -->
  <div class="filter-bar" style="grid-template-columns: 2fr 1fr auto;">
    <div class="filter-group">
      <label for="ajaxSearchInput">Live Search (Debounced 300ms)</label>
      <input type="text" id="ajaxSearchInput" class="filter-input" placeholder="Search events asynchronously...">
    </div>

    <div class="filter-group">
      <label for="ajaxCategoryFilter">Filter Category</label>
      <select id="ajaxCategoryFilter" class="filter-select">
        <option value="all">All Categories</option>
        <option value="Technical">Technical</option>
        <option value="Hackathon">Hackathon</option>
        <option value="Workshop">Workshop</option>
        <option value="Seminar">Seminar</option>
        <option value="Cultural">Cultural</option>
        <option value="Sports">Sports</option>
        <option value="Career">Career</option>
      </select>
    </div>

    <div style="align-self:flex-end;">
      <span class="badge badge-success" style="padding:10px 14px; font-size:0.85rem;">● API Endpoint: <code>/api/events.php</code></span>
    </div>
  </div>

  <!-- Dynamic AJAX Table -->
  <div class="table-card">
    <div class="table-header">
      <h3 style="font-size:1.2rem; font-weight:700;">Live Event Data (Rendered from JSON API)</h3>
      <span class="badge badge-neutral" id="ajaxRecordCount">Async Loaded</span>
    </div>

    <div class="table-responsive">
      <table class="data-table" aria-label="Asynchronously Loaded Events">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title & Venue</th>
            <th>Category</th>
            <th>Event Date</th>
            <th>Seats Filled</th>
            <th>Status</th>
            <th>Async Actions</th>
          </tr>
        </thead>
        <tbody id="ajaxEventsTableBody">
          <!-- Populated dynamically by ajax_crud.js -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Practical 13 Viva Info Card -->
  <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:28px; margin-top:30px;">
    <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:12px;">💡 How Practical 13 Works Under the Hood:</h3>
    <ul style="padding-left:20px; line-height:1.8; color:var(--text-muted); font-size:0.95rem;">
      <li><strong>REST JSON Endpoints:</strong> <code>api/events.php</code> responds to HTTP <code>GET</code>, <code>POST</code>, <code>PUT</code>, <code>DELETE</code> with strict JSON MIME type and proper HTTP status codes (200 OK, 201 Created, 400 Bad Request, 404 Not Found, 409 Conflict).</li>
      <li><strong>Debounced AJAX Search:</strong> Queries the backend 300 milliseconds after the user finishes typing, reducing server query overhead.</li>
      <li><strong>Asynchronous DOM Mutex:</strong> Newly created rows and deleted rows update the browser DOM instantly without interrupting user workflow or triggering full page refreshes.</li>
    </ul>
  </div>
</div>

<!-- Modal: Add Event via Fetch API -->
<div class="modal-overlay" id="ajaxAddModal" role="dialog" aria-modal="true" aria-labelledby="ajaxAddTitle">
  <div class="modal-dialog" style="max-width:560px;">
    <div class="modal-header">
      <h3 id="ajaxAddTitle">Add Event (Async Fetch POST)</h3>
      <button type="button" class="modal-close" data-close-modal>&times;</button>
    </div>
    <form id="ajaxAddEventForm">
      <div class="form-group">
        <label for="ajax_title" class="form-label">Event Title <span class="required">*</span></label>
        <input type="text" id="ajax_title" class="form-control" placeholder="e.g., Cloud Native DevOps Summit" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="ajax_category" class="form-label">Category</label>
          <select id="ajax_category" class="form-control" required>
            <option value="Technical">Technical</option>
            <option value="Hackathon">Hackathon</option>
            <option value="Workshop">Workshop</option>
            <option value="Seminar">Seminar</option>
            <option value="Cultural">Cultural</option>
          </select>
        </div>
        <div class="form-group">
          <label for="ajax_seats" class="form-label">Max Seats</label>
          <input type="number" id="ajax_seats" class="form-control" value="120" min="10">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="ajax_date" class="form-label">Date <span class="required">*</span></label>
          <input type="date" id="ajax_date" class="form-control" value="2026-11-25" required>
        </div>
        <div class="form-group">
          <label for="ajax_time" class="form-label">Time</label>
          <input type="time" id="ajax_time" class="form-control" value="10:00">
        </div>
      </div>

      <div class="form-group">
        <label for="ajax_venue" class="form-label">Venue <span class="required">*</span></label>
        <input type="text" id="ajax_venue" class="form-control" placeholder="e.g., FTE Lab 401" required>
      </div>

      <div class="form-group">
        <label for="ajax_desc" class="form-label">Description</label>
        <textarea id="ajax_desc" class="form-control" rows="2" placeholder="Brief summary of the session..."></textarea>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
        <button type="submit" class="btn btn-primary">Dispatch Fetch POST &rarr;</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
