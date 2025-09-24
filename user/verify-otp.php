<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Dev helper: set environment variable DEV_SHOW_OTP=1 to display the OTP on the page for testing only
// Only enable dev OTP display when explicitly requested via env var.
$envFlag = getenv('DEV_SHOW_OTP');
$DEV_SHOW_OTP = false;
if ($envFlag && in_array(strtolower($envFlag), ['1', 'true', 'yes'], true)) {
  $DEV_SHOW_OTP = true;
}

// If no reset requested, redirect back to forgot page
if (empty($_SESSION['reset_code']) || empty($_SESSION['reset_email'])) {
  header('Location: forgot-password.php');
  exit;
}

// Handle OTP verify
if (isset($_POST['verify_code'])) {
  $input_code = trim($_POST['input_code']);
  if (time() > $_SESSION['reset_expires']) {
    unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
    $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Reset code expired. Please request a new code.'];
    header('Location: forgot-password.php');
    exit;
  }

  if ($input_code == $_SESSION['reset_code']) {
    // Verified - proceed to reset password page
    header('Location: reset-password.php');
    exit;
  } else {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid code. Please check the code sent to your email.'];
    header('Location: verify-otp.php');
    exit;
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Verify Reset Code</title>
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper">
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-12 grid-margin stretch-card d-flex justify-content-center">
              <div class="card mx-auto" style="max-width:420px;">
                <div class="card-body">
                  <h4 class="card-title text-center">Verify Reset Code</h4>
                  <p class="text-muted text-center">Enter the 6-digit code sent to <strong><?php echo htmlentities($_SESSION['reset_email']);?></strong></p>
                  <?php if (!empty($_SESSION['flash'])): ?>
                    <div class="alert alert-<?php echo htmlentities($_SESSION['flash']['type']); ?>" role="alert">
                      <?php echo htmlentities($_SESSION['flash']['msg']); ?>
                    </div>
                    <?php unset($_SESSION['flash']); ?>
                  <?php endif; ?>
                  <form class="forms-sample" method="post" novalidate>
                    <div class="form-group">
                      <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="icon-key"></i></span></div>
                        <input type="text" class="form-control" name="input_code" placeholder="Reset Code" required="true" pattern="\d{6}" title="Enter the 6-digit code" autofocus>
                      </div>
                      <small class="form-text text-muted">Check your inbox (and spam) for the 6-digit code.</small>
                    </div>
                    <?php if ($DEV_SHOW_OTP && !empty($_SESSION['reset_code'])): ?>
                      <div class="alert alert-info">Dev OTP: <strong><?php echo htmlentities($_SESSION['reset_code']);?></strong></div>
                    <?php endif; ?>
                    <button type="submit" name="verify_code" class="btn btn-success">Verify Code</button>
                    <a href="forgot-password.php" class="btn btn-light">Change Email</a>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="vendors/js/vendor.bundle.base.js"></script>
  <script src="js/off-canvas.js"></script>
  <script src="js/misc.js"></script>
</body>
</html>
