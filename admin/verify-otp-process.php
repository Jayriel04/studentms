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
  <meta charset="utf-8">
  <title>Admin || Verify Reset Code</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth">
        <div class="row flex-grow">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left p-5">
              <div class="brand-logo" align="center" style="font-weight:bold">Admin - Verify Code</div>
              <h6 class="font-weight-light">Enter the 6-digit code sent to
                <strong><?php echo htmlentities($_SESSION['admin_fp_reset_email']); ?></strong></h6>
              <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlentities($error); ?></div><?php endif; ?>
              <form class="pt-3" method="post">
                <div class="form-group">
                  <input type="text" class="form-control form-control-lg" name="otp" placeholder="6-digit code" required
                    pattern="\d{6}" maxlength="6" inputmode="numeric" autofocus>
                </div>
                <div class="mt-3">
                  <button class="btn btn-success btn-block loginbtn" type="submit">Verify Code</button>
                </div>
                <div class="my-2 d-flex justify-content-between align-items-center">
                  <a href="forgot-process.php" class="auth-link text-black">Request new code</a>
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
</body>

</html>