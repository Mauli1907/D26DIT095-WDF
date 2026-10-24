<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$pageTitle = 'Campus Events - StudentHub | CHARUSAT FTE';
$activePage = 'events';
$breadcrumbs = ['Events' => ''];

// Practical 3, 6 & Wireframe 3: Server-side search & filtering with pagination
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? 'all');
$sortBy = trim($_GET['sort'] ?? 'date_asc');
$page = max(1, (int)($_GET['p'] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;

// Build query
$where = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (title LIKE ? OR description LIKE ? OR venue LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category) && $category !== 'all') {
    $where .= " AND category = ?";
    $params[] = $category;
}

$order = "ORDER BY event_date ASC";
if ($sortBy === 'date_desc') $order = "ORDER BY event_date DESC";
if ($sortBy === 'title_asc') $order = "ORDER BY title ASC";

// Get total count
$countRow = db_fetch_one("SELECT COUNT(*) as total FROM events $where", $params);
$totalEvents = $countRow['total'] ?? 0;
$totalPages = max(1, ceil($totalEvents / $limit));

// Fetch paginated events
$sql = "SELECT * FROM events $where $order LIMIT $limit OFFSET $offset";
$events = db_fetch_all($sql, $params);

// Distinct categories for filter dropdown
$categories = db_fetch_all("SELECT DISTINCT category FROM events ORDER BY category ASC");

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:30px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
      <h1 style="font-size:2.2rem; font-weight:800; margin-bottom:4px;">Campus Events & Hackathons</h1>
      <p style="color:var(--text-muted);">Explore workshops, competitions, cultural festivals, and technical symposiums.</p>
    </div>
    <div style="display:flex; gap:10px;">
      <a href="ajax_demo.php" class="btn btn-sm btn-outline">⚡ Live Fetch API Playground (Prac 13)</a>
    </div>
  </div>

  <!-- Wireframe 3: Search bar + category/date filter -->
  <form method="GET" action="events.php" class="filter-bar" role="search" aria-label="Events Filter">
    <div class="filter-group">
      <label for="eventSearch">Search Events</label>
      <input type="text" id="eventSearch" name="search" class="filter-input" placeholder="Search by title, keyword, or venue..." value="<?= htmlspecialchars($search) ?>">
    </div>

    <div class="filter-group">
      <label for="categoryFilter">Category</label>
      <select id="categoryFilter" name="category" class="filter-select">
        <option value="all">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['category']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="filter-group">
      <label for="sortFilter">Sort By</label>
      <select id="sortFilter" name="sort" class="filter-select">
        <option value="date_asc" <?= $sortBy === 'date_asc' ? 'selected' : '' ?>>Date: Earliest First</option>
        <option value="date_desc" <?= $sortBy === 'date_desc' ? 'selected' : '' ?>>Date: Latest First</option>
        <option value="title_asc" <?= $sortBy === 'title_asc' ? 'selected' : '' ?>>Title: A - Z</option>
      </select>
    </div>

    <div style="display:flex; gap:8px; align-self:flex-end;">
      <button type="submit" class="btn btn-primary">Apply Filter</button>
      <?php if (!empty($search) || $category !== 'all'): ?>
        <a href="events.php" class="btn btn-secondary" title="Reset Filters">Reset</a>
      <?php endif; ?>
    </div>
  </form>

  <!-- Wireframe 3: Event cards: poster, title, date, venue, view link -->
  <?php if (empty($events)): ?>
    <div style="text-align:center; padding:60px 20px; background:var(--bg-card); border-radius:var(--radius); border:1px dashed var(--border); margin-bottom:50px;">
      <p style="font-size:2.5rem; margin-bottom:10px;">🔍</p>
      <h3 style="margin-bottom:6px;">No events matched your search</h3>
      <p style="color:var(--text-muted); margin-bottom:20px;">Try adjusting your keywords or clearing the category filter.</p>
      <a href="events.php" class="btn btn-secondary">View All Events</a>
    </div>
  <?php else: ?>
    <div class="events-grid">
      <?php foreach ($events as $event): ?>
        <article class="event-card">
          <div class="event-card-poster">
            <img src="../assets/images/<?= htmlspecialchars($event['poster_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" loading="lazy">
            <span class="event-badge"><?= htmlspecialchars($event['category']) ?></span>
          </div>
          <div class="event-card-body">
            <div class="event-meta">
              <span>📅 <?= date('M d, Y', strtotime($event['event_date'])) ?></span>
              <span>⏰ <?= date('h:i A', strtotime($event['event_time'])) ?></span>
              <span>📍 <?= htmlspecialchars($event['venue']) ?></span>
            </div>
            <h3 class="event-title"><?= htmlspecialchars($event['title']) ?></h3>
            <p class="event-desc"><?= htmlspecialchars($event['description']) ?></p>
            <div class="event-card-footer">
              <span class="seats-info"><?= $event['max_seats'] - $event['registered_count'] ?> seats remaining</span>
              <a href="event_detail.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-primary">View Details &rarr;</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <!-- Wireframe 3: Pagination -->
    <?php if ($totalPages > 1): ?>
      <nav aria-label="Events pagination" style="margin-bottom:60px;">
        <ul class="pagination">
          <li>
            <a href="?p=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&sort=<?= urlencode($sortBy) ?>" 
               class="pagination-btn <?= $page <= 1 ? 'disabled' : '' ?>">
              &laquo; Previous
            </a>
          </li>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li>
              <a href="?p=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&sort=<?= urlencode($sortBy) ?>" 
                 class="pagination-btn <?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
              </a>
            </li>
          <?php endfor; ?>
          <li>
            <a href="?p=<?= min($totalPages, $page + 1) ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&sort=<?= urlencode($sortBy) ?>" 
               class="pagination-btn <?= $page >= $totalPages ? 'disabled' : '' ?>">
              Next &raquo;
            </a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
