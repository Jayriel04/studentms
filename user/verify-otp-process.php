<?php
session_start();
error_reporting(0);

// Redirect back if no OTP requested
if (empty($_SESSION['fp_reset_code']) || empty($_SESSION['fp_reset_email'])) {
  header('Location: forgot-process.php');
  exit;
}

$display_code = null;
// Dev-only: show code when environment flag set
$env = getenv('DEV_SHOW_OTP');
if ($env && in_array(strtolower($env), ['1', 'true', 'yes'], true)) {
  $display_code = $_SESSION['fp_reset_code'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
  $input = trim($_POST['otp']);
  if (time() > intval($_SESSION['fp_reset_expires'])) {
    unset($_SESSION['fp_reset_code'], $_SESSION['fp_reset_email'], $_SESSION['fp_reset_expires']);
    $error = 'Code expired. Please request a new one.';
  } else if ($input === (string) $_SESSION['fp_reset_code']) {
    // Verified
    $_SESSION['fp_verified'] = true;
    header('Location: reset-password-process.php');
    exit;
  } else {
    $error = 'Invalid code. Please try again.';
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Code | Student Profiling System</title>
  <link rel="icon" href="../images/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" sizes="180x180" href="../images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon-16x16.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../images/android-chrome-192x192.png">
  <link rel="manifest" href="../images/site.webmanifest">
  <link rel="stylesheet" href="css/login-new.css">
</head>

<body>
  <div class="container">
    <div class="welcome-section">
      <div class="welcome-content">
        <h1>VERIFY YOUR IDENTITY</h1>
        <p class="headline">Student Profiling System</p>
        <p>A secure 6-digit code has been sent to your email address. Please enter it to proceed with resetting your
          password.</p>
      </div>
      <div class="circle-decoration"></div>
    </div>

    <div class="form-section">
      <h2>Verify Code</h2>
      <p class="subtitle">Enter the 6-digit code sent to
        <strong><?php echo htmlentities($_SESSION['fp_reset_email']); ?></strong>.
      </p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"
          style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;">
          <?php echo htmlentities($error); ?></div>
      <?php endif; ?>

      <?php if (!empty($display_code)): ?>
        <div class="alert alert-info"
          style="color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;">
          Dev OTP: <strong><?php echo htmlentities($display_code); ?></strong></div>
      <?php endif; ?>

      <form method="post">
        <div class="input-group">
          <div class="input-wrapper">
            <span class="icon">#</span>
            <input type="text" id="otp" name="otp" placeholder="6-digit code" required pattern="\d{6}" maxlength="6"
              inputmode="numeric" autofocus>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Verify Code</button>
        <a href="forgot-process.php" class="btn btn-secondary">Request New Code</a>

      </form>
    </div>
  </div>
</body>

</html>