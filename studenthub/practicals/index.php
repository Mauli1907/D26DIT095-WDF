<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$pageTitle = 'Practicals Laboratory Hub (Practicals 2-15) - StudentHub';
$activePage = 'practicals';
$breadcrumbs = ['Practicals Lab Hub' => ''];

$practicals = [
    [
        'no' => 2,
        'title' => 'Semantic HTML5 Pages with Accessibility-Ready Structure',
        'concept' => 'Semantic HTML5, ARIA landmarks, WCAG 2.1 accessibility',
        'co' => 'CO1, CO2',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_2.php',
        'site_pages' => ['Home (index.php)', 'About (about.php)', 'Events (events.php)', 'Contact (contact.php)', 'FAQ (faq.php)'],
        'intermediate' => 'Breadcrumb navigation with structured breadcrumb hierarchy on all pages.',
        'advanced' => 'Skip-to-content keyboard link (.skip-link) and full keyboard accessibility focus rings.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 3,
        'title' => 'Responsive UI Design using CSS Grid, Flexbox, and Bootstrap/Modern CSS',
        'concept' => 'CSS Grid, Flexbox, Media Queries, Mobile-First layout',
        'co' => 'CO2',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_3.php',
        'site_pages' => ['All 10 Wireframe pages with mobile-first CSS Grid & Flexbox'],
        'intermediate' => 'Contact & Feedback pages with responsive split layout.',
        'advanced' => 'Reusable UI layout components, CSS custom properties (variables), card grids.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 4,
        'title' => 'JavaScript DOM Manipulation, Event Handling, and UI Interactivity',
        'concept' => 'DOM APIs, event listeners, localStorage theme persistence, UI animations',
        'co' => 'CO3, CO4',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_4.php',
        'site_pages' => ['FAQ Accordion', 'Image Slider (main.js)', 'Notification Banner', 'Modal Popups', 'Theme Switcher'],
        'intermediate' => 'Smooth transition effects to cards, buttons, and sliding panels.',
        'advanced' => 'User UI preferences stored in localStorage and automatically restored on page load.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 5,
        'title' => 'Registration Form with Frontend Validation and User-Friendly Error Handling',
        'concept' => 'HTML5 forms, RegEx validation, Password meter, Canvas CAPTCHA',
        'co' => 'CO1, CO3',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_5.php',
        'site_pages' => ['Registration Form (register.php)', 'validation.js'],
        'intermediate' => 'Real-time validation on keyup and change input events.',
        'advanced' => 'Custom CAPTCHA drawn dynamically on HTML5 Canvas with noise and reload.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 6,
        'title' => 'Rendering External JSON Data using Fetch API, Search and Filter',
        'concept' => 'JSON, Fetch API, array filtering/mapping/sorting, pagination',
        'co' => 'CO3, CO4',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_6.php',
        'site_pages' => ['events.json (16 records)', 'students.json (20 records)', 'faqs.json (15 records)', 'fetch_data.js'],
        'intermediate' => 'Dependent dropdowns (Department -> Course selection).',
        'advanced' => 'Offline caching of fetched JSON responses in localStorage with timestamp expiration.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 7,
        'title' => 'PHP Form Processing with Server-Side Validation and CSV/JSON File Storage',
        'concept' => 'PHP POST handling, sanitization, file writing with LOCK_EX, CSV & JSON storage',
        'co' => 'CO1, CO5',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_7.php',
        'site_pages' => ['Contact Form (contact.php)', 'Submissions View (submissions_view.php)', 'data/submissions.csv', 'data/submissions.json'],
        'intermediate' => 'Display stored CSV/JSON records on a dedicated web page table.',
        'advanced' => 'CSRF token generation and validation for form submission.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 8,
        'title' => 'MySQL Schema Design, ER Model, PDO Connectivity, and Prepared Statements',
        'concept' => 'Database normalization, relational integrity, PDO & MySQLi prepared statements',
        'co' => 'CO1, CO5',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_8.php',
        'site_pages' => ['database.sql (7 tables, seed data)', 'config/db.php (PDO & MySQLi wrappers)'],
        'intermediate' => 'PHP PDO and parameterized prepared statements.',
        'advanced' => 'Stored procedures: sp_GetStudentRegistrationSummary and sp_RegisterStudentForEvent.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 9,
        'title' => 'Secure User Registration with Database Insert, Duplicate Email Check, and Password Hashing',
        'concept' => 'password_hash(BCRYPT), duplicate email prevention, SQL injection defense',
        'co' => 'CO1, CO3, CO5',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_9.php',
        'site_pages' => ['register.php backend', 'users & students table linkage', 'audit_logs entry'],
        'intermediate' => 'Prepared statements with transactions ensuring multi-table consistency.',
        'advanced' => 'CSRF token protection and audit log entry recorded on registration.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 10,
        'title' => 'Secure Login/Logout with Sessions, Role-Based Access, Timeout, and Remember-Me Token',
        'concept' => 'PHP sessions, session_regenerate_id, password_verify, RBAC, session timeout',
        'co' => 'CO1, CO5',
        'hours' => '2 hrs implementation | 6 hrs total',
        'link' => 'practical_10.php',
        'site_pages' => ['login.php', 'logout.php', 'config/auth.php', 'student_dashboard.php', 'admin_dashboard.php'],
        'intermediate' => 'Last-login timestamp recorded in database on each login.',
        'advanced' => 'Persistent remember-me token stored as SHA-256 hash in database with secure HTTP-only cookies.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 11,
        'title' => 'Student Management CRUD with Search, Filter, and MySQLi Statements',
        'concept' => 'PHP-MySQLi CRUD, prepared statements, search, filter, UI confirmation',
        'co' => 'CO1, CO4, CO5',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_11.php',
        'site_pages' => ['admin_students.php (21 seeded students)', 'export_students_csv.php'],
        'intermediate' => 'Export student list directly to CSV spreadsheet.',
        'advanced' => 'Soft delete using status column (status = "deleted") and trash restore.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 12,
        'title' => 'Event Management CRUD with Poster Upload and File Validation',
        'concept' => 'File upload, MIME type verification, file size cap, directory protection',
        'co' => 'CO1, CO4, CO5',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_12.php',
        'site_pages' => ['admin_events.php', 'uploads/posters/.htaccess / index.html protection', 'events table'],
        'intermediate' => 'Event category and open/closed/upcoming status filter.',
        'advanced' => 'Unique safe file renaming (uniqid), upload directory access protection, and audit log entries.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 13,
        'title' => 'PHP JSON API and AJAX/Fetch-Based CRUD without Page Reload',
        'concept' => 'REST JSON API, JavaScript Fetch API, asynchronous DOM mutation, HTTP status codes',
        'co' => 'CO3, CO4, CO5',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_13.php',
        'site_pages' => ['api/events.php', 'api/students.php', 'ajax_demo.php', 'ajax_crud.js'],
        'intermediate' => 'AJAX live search with debounce timer (300ms).',
        'advanced' => 'Centralized HTTP status code handling (200, 201, 400, 404, 409, 500) and toast error banners.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 14,
        'title' => 'Admin Dashboard with Role-Based Access Control, Dynamic Menu, and Audit Log',
        'concept' => 'RBAC dashboard, SQL aggregation, audit logging, canvas analytics chart',
        'co' => 'CO1, CO4, CO5',
        'hours' => '4 hrs implementation | 6 hrs total',
        'link' => 'practical_14.php',
        'site_pages' => ['admin_dashboard.php', 'admin_audit.php', 'audit_logs table'],
        'intermediate' => 'Active/inactive user status toggle button.',
        'advanced' => 'Visual analytics bar chart rendered dynamically using HTML5 Canvas.',
        'status' => 'Completed & Verified'
    ],
    [
        'no' => 15,
        'title' => 'Final Integration, Testing, Documentation, GitHub Submission, and Local Deployment',
        'concept' => 'Full-stack integration, end-to-end testing, accessibility audit, deployment guide',
        'co' => 'CO2, CO4, CO5',
        'hours' => '4 hrs implementation | 4 hrs total',
        'link' => 'practical_15.php',
        'site_pages' => ['Complete StudentHub portal', 'database.sql', 'README.md', 'Test Report'],
        'intermediate' => 'Local deployment on XAMPP / Apache Virtual Host.',
        'advanced' => 'Complete documentation, ER diagram, accessibility checklist, and viva questions.',
        'status' => 'Completed & Verified'
    ]
];

require_once __DIR__ . '/../pages/includes/header.php';
?>

<div class="container" style="padding-top:20px; margin-bottom:70px;">
  <!-- Header -->
  <div class="section-header" style="text-align:left; margin-bottom:30px;">
    <span class="badge badge-primary">Course ITUE203 &bull; CHARUSAT FTE</span>
    <h1 style="font-size:2.4rem; font-weight:800; margin-top:8px; margin-bottom:8px;">Laboratory Practicals Hub (Practicals 2 to 15)</h1>
    <p style="color:var(--text-muted); font-size:1.05rem; max-width:850px;">
      Complete semester practical laboratory workbook for <strong>Web Development Frameworks</strong>.
      Every practical includes detailed problem definitions, technology concepts, live interactive demos, intermediate/advanced extensions, and laboratory viva explanations.
    </p>
  </div>

  <!-- Progress Bar -->
  <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:24px; margin-bottom:36px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
    <div>
      <h3 style="font-size:1.15rem; margin-bottom:4px;">Implementation Status: <span style="color:var(--success);">14 / 14 Practicals Completed (100%)</span></h3>
      <p style="color:var(--text-muted); font-size:0.9rem;">Practicals 2 through 15 implemented and integrated into the StudentHub website.</p>
    </div>
    <div style="display:flex; gap:10px;">
      <a href="../pages/index.php" class="btn btn-primary">&larr; Return to Main Website</a>
      <a href="../setup_database.php" class="btn btn-secondary">⚡ Re-seed Database</a>
    </div>
  </div>

  <!-- Practicals Table / Cards Grid -->
  <div style="display:flex; flex-direction:column; gap:20px;">
    <?php foreach ($practicals as $p): ?>
      <article class="highlight-card" style="padding:28px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:12px;">
          <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:44px; height:44px; border-radius:10px; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.2rem;">
              <?= $p['no'] ?>
            </div>
            <div>
              <h2 style="font-size:1.35rem; font-weight:800; margin:0;"><?= htmlspecialchars($p['title']) ?></h2>
              <span style="font-size:0.85rem; color:var(--text-muted); font-weight:600;">CO Mapping: <?= htmlspecialchars($p['co']) ?> &bull; <?= htmlspecialchars($p['hours']) ?></span>
            </div>
          </div>
          <span class="badge badge-success">✅ <?= htmlspecialchars($p['status']) ?></span>
        </div>

        <p style="color:var(--text-main); font-weight:600; margin-bottom:12px;">
          💡 Core Concept: <span style="font-weight:400; color:var(--text-muted);"><?= htmlspecialchars($p['concept']) ?></span>
        </p>

        <div style="background:var(--bg-alt); border-radius:var(--radius-sm); padding:14px 18px; margin-bottom:16px; font-size:0.9rem; line-height:1.7;">
          <div><strong>🌟 Intermediate Extension:</strong> <?= htmlspecialchars($p['intermediate']) ?></div>
          <div style="margin-top:6px;"><strong>🚀 Advanced Extension:</strong> <?= htmlspecialchars($p['advanced']) ?></div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-top:8px;">
          <div style="font-size:0.85rem; color:var(--text-muted);">
            <strong>Linked Pages:</strong> <?= htmlspecialchars(implode(', ', $p['site_pages'])) ?>
          </div>
          <div style="display:flex; gap:10px;">
            <a href="<?= htmlspecialchars($p['link']) ?>" class="btn btn-sm btn-primary">Open Lab Guide & Viva Q&A &rarr;</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../pages/includes/footer.php'; ?>
