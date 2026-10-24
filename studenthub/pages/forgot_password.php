<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

$pageTitle = 'Password Recovery - StudentHub | CHARUSAT FTE';
$activePage = 'login';
$breadcrumbs = ['Login' => 'login.php', 'Password Recovery' => ''];

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $email = trim($_POST['email'] ?? '');
        if (!empty($email)) {
            $msg = "If an account exists for " . htmlspecialchars($email) . ", password reset instructions have been sent to your university email.";
            log_audit_action('PASSWORD_RESET_REQUEST', "Password reset requested for $email");
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <div class="auth-container" style="max-width:480px;">
    <div class="auth-header">
      <span class="badge badge-primary">Account Recovery</span>
      <h1>Forgot Password?</h1>
      <p>Enter your registered college email and we will help you reset your credentials.</p>
    </div>

    <?php if (!empty($msg)): ?>
      <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" action="forgot_password.php">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="recovery_email" class="form-label">University Email Address</label>
        <input type="email" id="recovery_email" name="email" class="form-control" placeholder="student@charusat.edu.in" required>
      </div>

      <div style="margin-top:24px;">
        <button type="submit" class="btn btn-primary btn-block">Send Reset Link &rarr;</button>
      </div>

      <p style="text-align:center; margin-top:20px; font-size:0.95rem;">
        Remember your password? <a href="login.php">Back to Login</a>
      </p>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
