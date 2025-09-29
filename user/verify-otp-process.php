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
if ($env && in_array(strtolower($env), ['1','true','yes'], true)) {
  $display_code = $_SESSION['fp_reset_code'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
  $input = trim($_POST['otp']);
  if (time() > intval($_SESSION['fp_reset_expires'])) {
    unset($_SESSION['fp_reset_code'], $_SESSION['fp_reset_email'], $_SESSION['fp_reset_expires']);
    $error = 'Code expired. Please request a new one.';
  } else if ($input === (string)$_SESSION['fp_reset_code']) {
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
  <meta charset="utf-8">
  <title>Student Management System || Verify Reset Code</title>
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth">
        <div class="row flex-grow">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left p-5">
              <div class="brand-logo" align="center" style="font-weight:bold">Student Management System</div>
              <h6 class="font-weight-light">Enter the 6-digit code sent to <strong><?php echo htmlentities($_SESSION['fp_reset_email']); ?></strong></h6>

              <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlentities($error); ?></div>
              <?php endif; ?>

              <?php if (!empty($display_code)): ?>
                <div class="alert alert-info">Dev OTP: <strong><?php echo htmlentities($display_code); ?></strong></div>
              <?php endif; ?>

              <form class="pt-3" method="post">
                <div class="form-group">
                  <input type="text" class="form-control form-control-lg" name="otp" placeholder="6-digit code" pattern="\d{6}" required autofocus>
                </div>
                <div class="mt-3">
                  <button class="btn btn-success btn-block loginbtn" type="submit">Verify Code</button>
                </div>
                <div class="my-2 d-flex justify-content-between align-items-center">
                  <a href="forgot-process.php" class="auth-link text-black">Change Email</a>
                  <a href="login.php" class="auth-link text-black">Sign in</a>
                </div>
              </form>
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
