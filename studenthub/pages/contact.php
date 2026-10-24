<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

$pageTitle = 'Contact & Feedback - StudentHub | CHARUSAT FTE';
$activePage = 'contact';
$breadcrumbs = ['Contact' => ''];

$errors = [];
$formData = [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

$currentUser = current_user();
if ($currentUser) {
    $formData['name'] = $currentUser['name'];
    $formData['email'] = $currentUser['email'];
}

// Prefill subject if passed via query param (e.g. from event detail)
if (!empty($_GET['subject'])) {
    $formData['subject'] = trim($_GET['subject']);
}

// Practical 7: PHP Form Processing with Server-Side Validation and CSV/JSON File Storage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification (Practical 7 Advanced)
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors['csrf'] = 'Invalid security token (CSRF). Please refresh.';
    }

    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['email'] = strtolower(trim($_POST['email'] ?? ''));
    $formData['subject'] = trim($_POST['subject'] ?? '');
    $formData['message'] = trim($_POST['message'] ?? '');

    // Server-side validation
    if (empty($formData['name']) || strlen($formData['name']) < 2) {
        $errors['name'] = 'Please enter your name (minimum 2 characters).';
    }

    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email address is required.';
    }

    if (empty($formData['subject']) || strlen($formData['subject']) < 3) {
        $errors['subject'] = 'Subject is required (minimum 3 characters).';
    }

    if (empty($formData['message']) || strlen($formData['message']) < 10) {
        $errors['message'] = 'Message must be at least 10 characters long.';
    }

    if (empty($errors)) {
        // Sanitize for file storage
        $sanitizedName = htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8');
        $sanitizedEmail = filter_var($formData['email'], FILTER_SANITIZE_EMAIL);
        $sanitizedSubject = htmlspecialchars($formData['subject'], ENT_QUOTES, 'UTF-8');
        $sanitizedMessage = htmlspecialchars($formData['message'], ENT_QUOTES, 'UTF-8');
        $timestamp = date('Y-m-d H:i:s');
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $dataDir = __DIR__ . '/../data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }

        $csvFile = $dataDir . '/submissions.csv';
        $jsonFile = $dataDir . '/submissions.json';
        $newId = time();

        // 1. Write to CSV File safely with LOCK_EX (Practical 7)
        $csvRow = [$newId, $sanitizedName, $sanitizedEmail, $sanitizedSubject, $sanitizedMessage, $timestamp, $clientIp];
        $fp = fopen($csvFile, 'a');
        if ($fp && flock($fp, LOCK_EX)) {
            fputcsv($fp, $csvRow);
            flock($fp, LOCK_UN);
            fclose($fp);
        }

        // 2. Write to JSON File safely (Practical 7)
        $jsonRecords = [];
        if (file_exists($jsonFile)) {
            $raw = file_get_contents($jsonFile);
            $jsonRecords = json_decode($raw, true) ?? [];
        }
        $jsonRecords[] = [
            'id' => $newId,
            'name' => $sanitizedName,
            'email' => $sanitizedEmail,
            'subject' => $sanitizedSubject,
            'message' => $sanitizedMessage,
            'submission_time' => $timestamp,
            'ip_address' => $clientIp
        ];
        file_put_contents($jsonFile, json_encode($jsonRecords, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

        // 3. Also insert into database contact_messages table
        try {
            db_query("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)",
                [$sanitizedName, $sanitizedEmail, $sanitizedSubject, $sanitizedMessage]);
        } catch (Exception $e) {
            error_log("DB contact log failed: " . $e->getMessage());
        }

        // 4. Log audit action
        log_audit_action('FEEDBACK_SUBMISSION', "Feedback submitted by $sanitizedName: $sanitizedSubject", $currentUser['id'] ?? null, $currentUser['role'] ?? 'guest');

        set_flash('success', 'Thank you! Your feedback/query has been submitted and stored successfully.');
        header('Location: contact.php?status=sent');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <!-- Wireframe 9: Contact / feedback layout -->
  <div class="contact-layout">
    <!-- College address, phone, email -->
    <aside class="contact-info-card">
      <span class="badge badge-primary" style="margin-bottom:12px;">Get in Touch</span>
      <h2 style="font-size:1.8rem; font-weight:800; margin-bottom:12px;">Faculty of Technology & Engineering</h2>
      <p style="color:var(--text-muted); font-size:0.95rem; margin-bottom:28px;">
        Have queries regarding admissions, events, practical laboratories, or portal support? Reach out to us.
      </p>

      <div class="contact-meta-item">
        <div class="contact-meta-icon">📍</div>
        <div>
          <h4 style="font-size:1.05rem; margin-bottom:4px;">Campus Address</h4>
          <p style="color:var(--text-muted); font-size:0.9rem;">
            Faculty of Technology and Engineering (FTE)<br>
            Charotar University of Science and Technology (CHARUSAT)<br>
            Changa, Anand, Gujarat - 388421, India
          </p>
        </div>
      </div>

      <div class="contact-meta-item">
        <div class="contact-meta-icon">📞</div>
        <div>
          <h4 style="font-size:1.05rem; margin-bottom:4px;">Phone Numbers</h4>
          <p style="color:var(--text-muted); font-size:0.9rem;">
            Office: +91 2697 265011 / 265021<br>
            IT Department: Ext. 512 / 514
          </p>
        </div>
      </div>

      <div class="contact-meta-item">
        <div class="contact-meta-icon">✉️</div>
        <div>
          <h4 style="font-size:1.05rem; margin-bottom:4px;">Official Emails</h4>
          <p style="color:var(--text-muted); font-size:0.9rem;">
            General: <a href="mailto:info.fte@charusat.ac.in">info.fte@charusat.ac.in</a><br>
            StudentHub Support: <a href="mailto:studenthub@charusat.edu.in">studenthub@charusat.edu.in</a>
          </p>
        </div>
      </div>

      <div style="margin-top:24px; padding-top:20px; border-top:1px solid var(--border);">
        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:10px;">
          🔬 Practical 7 Demonstration:
        </p>
        <a href="submissions_view.php" class="btn btn-sm btn-outline">View Stored CSV / JSON Records &rarr;</a>
      </div>
    </aside>

    <!-- Wireframe 9: Name, email, subject, message textarea, send message -->
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:40px; box-shadow:var(--shadow-sm);">
      <h2 style="font-size:1.8rem; font-weight:800; margin-bottom:8px;">Send a Message / Feedback</h2>
      <p style="color:var(--text-muted); margin-bottom:28px;">
        Fill out this form. Submissions are processed and stored on the server via PHP (Practical 7).
      </p>

      <?php if (!empty($errors['csrf'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['csrf']) ?></div>
      <?php endif; ?>

      <form method="POST" action="contact.php" novalidate>
        <?= csrf_field() ?>

        <div class="form-row">
          <div class="form-group">
            <label for="contact_name" class="form-label">Your Name <span class="required">*</span></label>
            <input type="text" id="contact_name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                   value="<?= htmlspecialchars($formData['name']) ?>" required>
            <div class="error-msg"><?= $errors['name'] ?? '' ?></div>
          </div>

          <div class="form-group">
            <label for="contact_email" class="form-label">Your Email <span class="required">*</span></label>
            <input type="email" id="contact_email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                   value="<?= htmlspecialchars($formData['email']) ?>" required>
            <div class="error-msg"><?= $errors['email'] ?? '' ?></div>
          </div>
        </div>

        <div class="form-group">
          <label for="contact_subject" class="form-label">Subject <span class="required">*</span></label>
          <input type="text" id="contact_subject" name="subject" class="form-control <?= isset($errors['subject']) ? 'is-invalid' : '' ?>" 
                 value="<?= htmlspecialchars($formData['subject']) ?>" placeholder="e.g., Question about Web Dev Hackathon" required>
          <div class="error-msg"><?= $errors['subject'] ?? '' ?></div>
        </div>

        <div class="form-group">
          <label for="contact_message" class="form-label">Message / Feedback <span class="required">*</span></label>
          <textarea id="contact_message" name="message" class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" 
                    rows="6" placeholder="Write your message here in detail..." required><?= htmlspecialchars($formData['message']) ?></textarea>
          <div class="error-msg"><?= $errors['message'] ?? '' ?></div>
        </div>

        <div style="margin-top:24px;">
          <button type="submit" class="btn btn-primary btn-lg">Send Message &rarr;</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
