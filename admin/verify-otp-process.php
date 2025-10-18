<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../includes/dbconnection.php';

// Redirect if no reset flow started
if (empty($_SESSION['admin_fp_reset_email']) || empty($_SESSION['admin_fp_reset_code'])) {
  header('Location: forgot-process.php');
  exit;
}

// Clear expired code
if (!empty($_SESSION['admin_fp_reset_expires']) && time() > intval($_SESSION['admin_fp_reset_expires'])) {
  unset($_SESSION['admin_fp_reset_code'], $_SESSION['admin_fp_reset_email'], $_SESSION['admin_fp_reset_expires'], $_SESSION['admin_fp_attempts']);
  $error = 'The reset code has expired. Please request a new one.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
  $otp = trim($_POST['otp']);

  if (!preg_match('/^\d{6}$/', $otp)) {
    $error = 'Enter a valid 6-digit code.';
  } else {
    $_SESSION['admin_fp_attempts'] = ($_SESSION['admin_fp_attempts'] ?? 0) + 1;

    if ($_SESSION['admin_fp_attempts'] > 5) {
      unset($_SESSION['admin_fp_reset_code'], $_SESSION['admin_fp_reset_email'], $_SESSION['admin_fp_reset_expires'], $_SESSION['admin_fp_attempts']);
      $error = 'Too many attempts. Please request a new code.';
    } else if (isset($_SESSION['admin_fp_reset_code']) && hash_equals((string) $_SESSION['admin_fp_reset_code'], $otp)) {
      unset($_SESSION['admin_fp_reset_code'], $_SESSION['admin_fp_reset_expires'], $_SESSION['admin_fp_attempts']);
      $_SESSION['admin_fp_verified'] = true;
      header('Location: reset-password-process.php');
      exit;
    } else {
      $error = 'Invalid code. Please check your email and try again.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code | Student Profiling System</title>
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="css/login-new.css">
</head>

<body>
    <div class="container">
        <div class="welcome-section">
            <div class="welcome-content">
                <h1>VERIFY YOUR IDENTITY</h1>
                <p class="headline">Student Profiling System</p>
                <p>A secure 6-digit code has been sent to your email address. Please enter it to proceed with resetting your password.</p>
            </div>
            <div class="circle-decoration"></div>
        </div>

        <div class="form-section">
            <h2>Verify Code</h2>
            <p class="subtitle">Enter the 6-digit code sent to
                <strong><?php echo htmlentities($_SESSION['admin_fp_reset_email']); ?></strong>.
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;"><?php echo htmlentities($error); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">#</span>
                        <input type="text" id="otp" name="otp" placeholder="6-digit code" required pattern="\d{6}" maxlength="6" inputmode="numeric" autofocus>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Verify Code</button>
                <a href="forgot-process.php" class="btn btn-secondary">Request New Code</a>

            </form>
        </div>
    </div>
</body>

</html>