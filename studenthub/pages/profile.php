<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

require_auth();

$currentUser = current_user();
$userId = $currentUser['id'];

$pageTitle = 'My Profile - StudentHub | CHARUSAT FTE';
$activePage = 'profile';
$breadcrumbs = ['Dashboard' => 'student_dashboard.php', 'Profile' => ''];

// Fetch or create student record
$student = db_fetch_one("SELECT * FROM students WHERE user_id = ?", [$userId]);
$userRecord = db_fetch_one("SELECT * FROM users WHERE id = ?", [$userId]);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors['csrf'] = 'Invalid CSRF token.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $year = trim($_POST['year_of_study'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        if (empty($fullName) || !preg_match("/^[a-zA-Z\s.]{3,50}$/", $fullName)) {
            $errors['full_name'] = 'Full name must contain letters only (3-50 chars).';
        }

        if (empty($mobile) || !preg_match("/^[6-9]\d{9}$/", $mobile)) {
            $errors['mobile'] = 'Enter a valid 10-digit mobile number.';
        }

        if (empty($course)) {
            $errors['course'] = 'Please select your department / course.';
        }

        if (empty($year)) {
            $errors['year_of_study'] = 'Please select your academic year.';
        }

        if (empty($errors)) {
            try {
                // Update users table
                db_query("UPDATE users SET full_name = ? WHERE id = ?", [$fullName, $userId]);
                $_SESSION['user_name'] = $fullName;

                // Update or Insert students table
                if ($student) {
                    db_query("UPDATE students SET full_name = ?, mobile = ?, course = ?, year_of_study = ?, address = ?, bio = ? WHERE user_id = ?",
                        [$fullName, $mobile, $course, $year, $address, $bio, $userId]);
                } else {
                    $stuId = 'STU' . rand(10000, 99999);
                    db_query("INSERT INTO students (user_id, student_id_number, full_name, email, mobile, course, year_of_study, gender, address, bio) VALUES (?, ?, ?, ?, ?, ?, ?, 'Male', ?, ?)",
                        [$userId, $stuId, $fullName, $userRecord['email'], $mobile, $course, $year, $address, $bio]);
                }

                log_audit_action('PROFILE_UPDATE', "User $fullName updated their profile details", $userId, $currentUser['role']);
                set_flash('success', 'Profile details updated successfully!');
                header('Location: profile.php');
                exit;
            } catch (Exception $e) {
                $errors['db'] = 'Failed to update profile: ' . $e->getMessage();
            }
        }
    }
}

// Reload current profile
$student = db_fetch_one("SELECT * FROM students WHERE user_id = ?", [$userId]);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <!-- Wireframe 8: Profile Card -->
  <div class="profile-card">
    <div style="margin-bottom:24px;">
      <h1 style="font-size:2rem; font-weight:800; margin-bottom:6px;">Student Profile</h1>
      <p style="color:var(--text-muted);">Manage your personal details and academic department information.</p>
    </div>

    <?php if (!empty($errors['db'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errors['db']) ?></div>
    <?php endif; ?>

    <form method="POST" action="profile.php">
      <?= csrf_field() ?>

      <!-- Wireframe 8: Photo + basic info group -->
      <div class="profile-header-group">
        <div>
          <img src="../assets/images/<?= htmlspecialchars($student['profile_pic'] ?? 'default_avatar.svg') ?>" 
               alt="Student Avatar" class="profile-avatar-lg" style="width:110px; height:110px;">
        </div>
        <div>
          <h2 style="font-size:1.4rem; margin-bottom:4px;"><?= htmlspecialchars($userRecord['full_name'] ?? '') ?></h2>
          <p style="color:var(--text-muted); font-size:0.95rem;">
            <?= htmlspecialchars($student['student_id_number'] ?? 'Student') ?> &bull; 
            <?= htmlspecialchars($student['course'] ?? 'FTE Department') ?>
          </p>
          <span class="badge badge-success" style="margin-top:6px;">Enrolled Student</span>
        </div>
      </div>

      <!-- Wireframe 8: Name, Email, Mobile -->
      <div class="form-row">
        <div class="form-group">
          <label for="prof_name" class="form-label">Full Name <span class="required">*</span></label>
          <input type="text" id="prof_name" name="full_name" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
                 value="<?= htmlspecialchars($userRecord['full_name'] ?? '') ?>" required>
          <div class="error-msg"><?= $errors['full_name'] ?? '' ?></div>
        </div>

        <div class="form-group">
          <label for="prof_email" class="form-label">Email Address (Read-only)</label>
          <input type="email" id="prof_email" class="form-control" 
                 value="<?= htmlspecialchars($userRecord['email'] ?? '') ?>" disabled style="background:var(--bg-alt); cursor:not-allowed;">
        </div>
      </div>

      <!-- Wireframe 8: Mobile, Course, Year -->
      <div class="form-row">
        <div class="form-group">
          <label for="prof_mobile" class="form-label">Mobile Number <span class="required">*</span></label>
          <input type="tel" id="prof_mobile" name="mobile" class="form-control <?= isset($errors['mobile']) ? 'is-invalid' : '' ?>" 
                 value="<?= htmlspecialchars($student['mobile'] ?? '') ?>" maxlength="10" required>
          <div class="error-msg"><?= $errors['mobile'] ?? '' ?></div>
        </div>

        <div class="form-group">
          <label for="prof_course" class="form-label">Department / Course <span class="required">*</span></label>
          <select id="prof_course" name="course" class="form-control <?= isset($errors['course']) ? 'is-invalid' : '' ?>" required>
            <?php
            $courseList = ['B.Tech IT', 'B.Tech CE', 'B.Tech CSE', 'B.Tech EC', 'BCA', 'MCA'];
            $curCourse = $student['course'] ?? '';
            foreach ($courseList as $c): ?>
              <option value="<?= $c ?>" <?= $curCourse === $c ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select>
          <div class="error-msg"><?= $errors['course'] ?? '' ?></div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="prof_year" class="form-label">Year of Study <span class="required">*</span></label>
          <select id="prof_year" name="year_of_study" class="form-control" required>
            <?php
            $years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
            $curYear = $student['year_of_study'] ?? '';
            foreach ($years as $y): ?>
              <option value="<?= $y ?>" <?= $curYear === $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="prof_address" class="form-label">Residential City / Address</label>
          <input type="text" id="prof_address" name="address" class="form-control" 
                 value="<?= htmlspecialchars($student['address'] ?? '') ?>" placeholder="e.g., Anand, Gujarat">
        </div>
      </div>

      <div class="form-group">
        <label for="prof_bio" class="form-label">Short Bio / Interests</label>
        <textarea id="prof_bio" name="bio" class="form-control" rows="3" placeholder="Tell us about your technical interests, hackathon goals, or hobbies..."><?= htmlspecialchars($student['bio'] ?? '') ?></textarea>
      </div>

      <!-- Wireframe 8: Save changes button -->
      <div style="margin-top:28px; display:flex; justify-content:flex-end; gap:12px;">
        <a href="student_dashboard.php" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
