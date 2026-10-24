<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

// If already logged in, redirect
if (is_logged_in()) {
    header('Location: student_dashboard.php');
    exit;
}

$pageTitle = 'Student Registration - StudentHub | CHARUSAT FTE';
$activePage = 'register';
$breadcrumbs = ['Register' => ''];
$extraScript = '../assets/js/validation.js';

$errors = [];
$formData = [
    'full_name' => '',
    'email' => '',
    'mobile' => '',
    'student_id' => '',
    'course' => '',
    'year_of_study' => '',
    'gender' => '',
    'terms' => false
];

// Practical 9: Secure Backend Registration Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token (Practical 9 Advanced)
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors['csrf'] = 'Security verification failed (Invalid CSRF Token). Please refresh and submit again.';
    }

    // 2. Sanitize and retrieve inputs
    $formData['full_name'] = trim($_POST['full_name'] ?? '');
    $formData['email'] = strtolower(trim($_POST['email'] ?? ''));
    $formData['mobile'] = trim($_POST['mobile'] ?? '');
    $formData['student_id'] = strtoupper(trim($_POST['student_id'] ?? ''));
    $formData['course'] = trim($_POST['course'] ?? '');
    $formData['year_of_study'] = trim($_POST['year_of_study'] ?? '');
    $formData['gender'] = trim($_POST['gender'] ?? '');
    $formData['terms'] = isset($_POST['terms']);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // 3. Server-side Validations (Consistent with Practical 5 Frontend)
    if (empty($formData['full_name']) || !preg_match("/^[a-zA-Z\s.]{3,50}$/", $formData['full_name'])) {
        $errors['full_name'] = 'Full name must contain only alphabets and spaces (3-50 characters).';
    }

    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid university email address is required.';
    }

    if (empty($formData['mobile']) || !preg_match("/^[6-9]\d{9}$/", $formData['mobile'])) {
        $errors['mobile'] = 'Enter a valid 10-digit Indian mobile number.';
    }

    if (empty($formData['student_id'])) {
        $formData['student_id'] = 'STU' . rand(10000, 99999);
    }

    if (empty($formData['course'])) {
        $errors['course'] = 'Please select your department / course.';
    }

    if (empty($formData['year_of_study'])) {
        $errors['year_of_study'] = 'Please select your academic year.';
    }

    if (empty($formData['gender']) || !in_array($formData['gender'], ['Male', 'Female', 'Other'])) {
        $errors['gender'] = 'Please select your gender.';
    }

    if (!$formData['terms']) {
        $errors['terms'] = 'You must accept the terms and conditions.';
    }

    // Password validation: Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 symbol
    if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[@$!%*?&#]/", $password)) {
        $errors['password'] = 'Password must be at least 8 characters with uppercase, lowercase, number, and special symbol (@$!%*?&#).';
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // 4. Duplicate Email / ID Check (Practical 9)
    if (empty($errors)) {
        $existingUser = db_fetch_one("SELECT id FROM users WHERE email = ?", [$formData['email']]);
        if ($existingUser) {
            $errors['email'] = 'An account with this email address already exists. Please log in.';
        }
    }

    // 5. Database Insert with Password Hashing & Prepared Statements (Practical 9)
    if (empty($errors)) {
        try {
            $pdo = get_db_connection();
            $pdo->beginTransaction();

            // Password hashing using BCrypt (Cost 10)
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Insert into users table
            $stmtUser = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, 'student', 'active')");
            $stmtUser->execute([$formData['full_name'], $formData['email'], $passwordHash]);
            $newUserId = (int)$pdo->lastInsertId();

            // Insert into students profile table
            $stmtStudent = $pdo->prepare("INSERT INTO students (user_id, student_id_number, full_name, email, mobile, course, year_of_study, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmtStudent->execute([
                $newUserId,
                $formData['student_id'],
                $formData['full_name'],
                $formData['email'],
                $formData['mobile'],
                $formData['course'],
                $formData['year_of_study'],
                $formData['gender']
            ]);

            $pdo->commit();

            // Log registration to audit log (Practical 9 & 14)
            log_audit_action('USER_REGISTRATION', "New student registered: {$formData['full_name']} ({$formData['email']})", $newUserId, 'student');

            set_flash('success', 'Registration successful! You can now log in with your credentials.');
            header('Location: login.php');
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Registration DB insert failed: " . $e->getMessage());
            $errors['db'] = 'Registration failed due to a database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <!-- Wireframe 5: Register Form -->
  <div class="auth-container">
    <div class="auth-header">
      <span class="badge badge-primary" style="margin-bottom:8px;">CHARUSAT FTE Portal</span>
      <h1>Student Registration</h1>
      <p>Create your student portal account to register for events and workshops.</p>
    </div>

    <?php if (!empty($errors['csrf'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errors['csrf']) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors['db'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errors['db']) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" id="registrationForm" novalidate>
      <?= csrf_field() ?>

      <!-- Full Name -->
      <div class="form-group">
        <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
        <input type="text" id="full_name" name="full_name" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
               placeholder="e.g., Aarav Sharma" value="<?= htmlspecialchars($formData['full_name']) ?>" required>
        <div class="error-msg" id="fullNameError"><?= $errors['full_name'] ?? '' ?></div>
      </div>

      <!-- Email & Mobile row -->
      <div class="form-row">
        <div class="form-group">
          <label for="email" class="form-label">College Email <span class="required">*</span></label>
          <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                 placeholder="e.g., student@charusat.edu.in" value="<?= htmlspecialchars($formData['email']) ?>" required>
          <div class="error-msg" id="emailError"><?= $errors['email'] ?? '' ?></div>
        </div>

        <div class="form-group">
          <label for="mobile" class="form-label">Mobile Number <span class="required">*</span></label>
          <input type="tel" id="mobile" name="mobile" class="form-control <?= isset($errors['mobile']) ? 'is-invalid' : '' ?>" 
                 placeholder="10-digit mobile number" value="<?= htmlspecialchars($formData['mobile']) ?>" maxlength="10" required>
          <div class="error-msg" id="mobileError"><?= $errors['mobile'] ?? '' ?></div>
        </div>
      </div>

      <!-- Student ID / Enrollment Number -->
      <div class="form-group">
        <label for="student_id" class="form-label">Student ID / Enrollment No. (Optional)</label>
        <input type="text" id="student_id" name="student_id" class="form-control" 
               placeholder="e.g., 23IT001 (auto-generated if empty)" value="<?= htmlspecialchars($formData['student_id']) ?>">
      </div>

      <!-- Course & Year row -->
      <div class="form-row">
        <div class="form-group">
          <label for="course" class="form-label">Department / Course <span class="required">*</span></label>
          <select id="course" name="course" class="form-control <?= isset($errors['course']) ? 'is-invalid' : '' ?>" required>
            <option value="">-- Select Course --</option>
            <option value="B.Tech IT" <?= $formData['course'] === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech Information Technology</option>
            <option value="B.Tech CE" <?= $formData['course'] === 'B.Tech CE' ? 'selected' : '' ?>>B.Tech Computer Engineering</option>
            <option value="B.Tech CSE" <?= $formData['course'] === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech Computer Science & Eng.</option>
            <option value="B.Tech EC" <?= $formData['course'] === 'B.Tech EC' ? 'selected' : '' ?>>B.Tech Electronics & Comm.</option>
            <option value="BCA" <?= $formData['course'] === 'BCA' ? 'selected' : '' ?>>BCA</option>
            <option value="MCA" <?= $formData['course'] === 'MCA' ? 'selected' : '' ?>>MCA</option>
          </select>
          <div class="error-msg" id="courseError"><?= $errors['course'] ?? '' ?></div>
        </div>

        <div class="form-group">
          <label for="year_of_study" class="form-label">Year of Study <span class="required">*</span></label>
          <select id="year_of_study" name="year_of_study" class="form-control <?= isset($errors['year_of_study']) ? 'is-invalid' : '' ?>" required>
            <option value="">-- Select Year --</option>
            <option value="1st Year" <?= $formData['year_of_study'] === '1st Year' ? 'selected' : '' ?>>1st Year</option>
            <option value="2nd Year" <?= $formData['year_of_study'] === '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
            <option value="3rd Year" <?= $formData['year_of_study'] === '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
            <option value="4th Year" <?= $formData['year_of_study'] === '4th Year' ? 'selected' : '' ?>>4th Year</option>
          </select>
          <div class="error-msg" id="year_of_studyError"><?= $errors['year_of_study'] ?? '' ?></div>
        </div>
      </div>

      <!-- Gender -->
      <div class="form-group">
        <label class="form-label">Gender <span class="required">*</span></label>
        <div style="display:flex; gap:24px; padding-top:4px;">
          <label class="checkbox-label">
            <input type="radio" name="gender" value="Male" <?= $formData['gender'] === 'Male' ? 'checked' : '' ?>> Male
          </label>
          <label class="checkbox-label">
            <input type="radio" name="gender" value="Female" <?= $formData['gender'] === 'Female' ? 'checked' : '' ?>> Female
          </label>
          <label class="checkbox-label">
            <input type="radio" name="gender" value="Other" <?= $formData['gender'] === 'Other' ? 'checked' : '' ?>> Other
          </label>
        </div>
        <div class="error-msg" id="genderError"><?= $errors['gender'] ?? '' ?></div>
      </div>

      <!-- Password & Confirm Password -->
      <div class="form-row">
        <div class="form-group">
          <label for="password" class="form-label">Password <span class="required">*</span></label>
          <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                 placeholder="Min 8 chars, 1 uppercase, 1 symbol" required>
          <!-- Practical 5: Password Strength Meter -->
          <div class="password-meter-wrap">
            <div class="meter-bar"><div class="meter-fill" id="passwordMeterFill"></div></div>
            <div class="meter-label" id="passwordMeterLabel">Strength: None</div>
          </div>
          <div class="error-msg" id="passwordError"><?= $errors['password'] ?? '' ?></div>
        </div>

        <div class="form-group">
          <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                 placeholder="Re-enter password" required>
          <div class="error-msg" id="confirmPasswordError"><?= $errors['confirm_password'] ?? '' ?></div>
        </div>
      </div>

      <!-- Practical 5 Advanced: HTML5 Canvas CAPTCHA -->
      <div class="form-group" style="padding:16px; background:var(--bg-alt); border-radius:var(--radius-sm); margin-bottom:20px;">
        <label for="captcha" class="form-label">Security Verification (Canvas CAPTCHA) <span class="required">*</span></label>
        <div class="captcha-box">
          <canvas id="captchaCanvas" width="170" height="46" class="captcha-canvas" aria-label="Visual CAPTCHA Challenge"></canvas>
          <button type="button" class="captcha-refresh-btn" id="refreshCaptchaBtn" title="Get new CAPTCHA">🔄 Refresh</button>
        </div>
        <input type="text" id="captcha" name="captcha" class="form-control" placeholder="Type the characters shown above" required autocomplete="off">
        <div class="error-msg" id="captchaError"></div>
      </div>

      <!-- Terms & Conditions checkbox -->
      <div class="form-group">
        <label class="checkbox-label" for="terms">
          <input type="checkbox" id="terms" name="terms" <?= $formData['terms'] ? 'checked' : '' ?> required>
          <span>I agree to the <a href="terms.php" target="_blank">Terms of Use</a> and acknowledge the CHARUSAT FTE student code of conduct.</span>
        </label>
        <div class="error-msg" id="termsError"><?= $errors['terms'] ?? '' ?></div>
      </div>

      <!-- Wireframe 5: Register button -->
      <div style="margin-top:28px;">
        <button type="submit" class="btn btn-primary btn-lg btn-block" id="registerSubmitBtn">Register Account &rarr;</button>
      </div>

      <p style="text-align:center; margin-top:20px; font-size:0.95rem; color:var(--text-muted);">
        Already have an account? <a href="login.php" style="font-weight:700;">Login here</a>
      </p>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
