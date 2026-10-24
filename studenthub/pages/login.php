<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

// If already logged in, redirect based on role
if (is_logged_in()) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: student_dashboard.php');
    }
    exit;
}

$pageTitle = 'Login - StudentHub | CHARUSAT FTE';
$activePage = 'login';
$breadcrumbs = ['Login' => ''];

$error = '';
$emailVal = trim($_POST['email'] ?? '');
$redirect = $_GET['redirect'] ?? '';

// Practical 10: Secure Login Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session validation failed (Invalid CSRF). Please refresh.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        if (empty($email) || empty($password)) {
            $error = 'Please enter both your email address and password.';
        } else {
            // Find user in database using prepared statement
            $user = db_fetch_one("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Verify account is active
                if ($user['status'] !== 'active') {
                    $error = 'Your account has been deactivated or suspended. Please contact the administrator.';
                    log_audit_action('FAILED_LOGIN_SUSPENDED', "Attempted login to suspended account: $email", $user['id'], $user['role']);
                } else {
                    // Practical 10: Regenerate session ID to prevent Session Fixation attacks
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['last_activity'] = time();

                    // Update last_login timestamp (Practical 10 Intermediate)
                    db_query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);

                    // Practical 10 Advanced: Remember-Me token handling
                    if ($rememberMe) {
                        $randomToken = bin2hex(random_bytes(32));
                        $hashedToken = hash('sha256', $randomToken);
                        
                        // Store hashed token in DB
                        db_query("UPDATE users SET remember_token = ? WHERE id = ?", [$hashedToken, $user['id']]);

                        // Set HTTP-Only persistent cookie (valid for 30 days)
                        $cookieValue = $user['id'] . ':' . $randomToken;
                        setcookie('studenthub_remember', $cookieValue, time() + (86400 * 30), '/', '', false, true);
                    }

                    // Log audit entry
                    log_audit_action('USER_LOGIN', "Successful login for {$user['email']} as {$user['role']}", $user['id'], $user['role']);

                    set_flash('success', "Welcome back, {$user['full_name']}!");

                    // Role-based redirection or custom redirect
                    if (!empty($redirect) && strpos($redirect, 'http') === false) {
                        header("Location: $redirect");
                    } else {
                        if ($user['role'] === 'admin') {
                            header('Location: admin_dashboard.php');
                        } else {
                            header('Location: student_dashboard.php');
                        }
                    }
                    exit;
                }
            } else {
                $error = 'Invalid email address or password. Please try again.';
                log_audit_action('FAILED_LOGIN', "Failed login attempt for email: $email");
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <!-- Wireframe 6: Login layout -->
  <div class="auth-container" style="max-width:500px;">
    <div class="auth-header">
      <span class="badge badge-primary" style="margin-bottom:8px;">Portal Sign-in</span>
      <h1>Welcome Back</h1>
      <p>Log in to access your registered events, profile, and student dashboard.</p>
    </div>

    <!-- Quick Credentials Helper Box for Viva / Demonstration -->
    <div style="background:var(--bg-alt); border:1px dashed var(--border); border-radius:var(--radius-sm); padding:14px; margin-bottom:20px; font-size:0.85rem;">
      <strong>🧪 Test Accounts for Demo / Evaluation:</strong><br>
      <div style="display:flex; justify-content:space-between; margin-top:6px; flex-wrap:wrap; gap:8px;">
        <button type="button" class="btn btn-sm btn-secondary" onclick="fillCreds('admin@charusat.edu.in', 'Admin@123')">
          Fill Admin Creds (admin@charusat.edu.in)
        </button>
        <button type="button" class="btn btn-sm btn-secondary" onclick="fillCreds('aarav.sharma@charusat.edu.in', 'Student@123')">
          Fill Student Creds (aarav.sharma@...)
        </button>
      </div>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>">
      <?= csrf_field() ?>

      <!-- Wireframe 6: Email field -->
      <div class="form-group">
        <label for="login_email" class="form-label">Email Address <span class="required">*</span></label>
        <input type="email" id="login_email" name="email" class="form-control" 
               placeholder="name@charusat.edu.in" value="<?= htmlspecialchars($emailVal) ?>" required autofocus>
      </div>

      <!-- Wireframe 6: Password field -->
      <div class="form-group">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <label for="login_password" class="form-label">Password <span class="required">*</span></label>
          <a href="forgot_password.php" style="font-size:0.85rem;">Forgot Password?</a>
        </div>
        <input type="password" id="login_password" name="password" class="form-control" 
               placeholder="Enter your password" required>
      </div>

      <!-- Remember-Me Checkbox (Practical 10) -->
      <div class="form-group">
        <label class="checkbox-label" for="remember_me">
          <input type="checkbox" id="remember_me" name="remember_me">
          <span>Remember me on this browser (Persistent Token)</span>
        </label>
      </div>

      <!-- Wireframe 6: Login button -->
      <div style="margin-top:24px;">
        <button type="submit" class="btn btn-primary btn-lg btn-block">Sign In &rarr;</button>
      </div>

      <!-- Wireframe 6: Links: forgot password, register -->
      <p style="text-align:center; margin-top:24px; font-size:0.95rem; color:var(--text-muted);">
        Don't have an account? <a href="register.php" style="font-weight:700;">Register here</a>
      </p>
    </form>
  </div>
</div>

<script>
function fillCreds(email, password) {
  document.getElementById('login_email').value = email;
  document.getElementById('login_password').value = password;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
