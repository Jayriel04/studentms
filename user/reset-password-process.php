<?php
session_start();
error_reporting(0);
include __DIR__ . '/../includes/dbconnection.php';

// Ensure verification step passed
if (empty($_SESSION['fp_verified']) || $_SESSION['fp_verified'] !== true || empty($_SESSION['fp_reset_email'])) {
  header('Location: forgot-process.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newpassword']) && isset($_POST['confirmpassword'])) {
  $new = $_POST['newpassword'];
  $conf = $_POST['confirmpassword'];

  if ($new !== $conf) {
    $error = 'Passwords do not match.';
  } else if (strlen($new) < 6) {
    $error = 'Password must be at least 6 characters.';
  } else {
    $hash = password_hash($new, PASSWORD_DEFAULT);
    $sql = "UPDATE tblstudent SET Password = :p WHERE EmailAddress = :e";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':p', $hash, PDO::PARAM_STR);
    $stmt->bindParam(':e', $_SESSION['fp_reset_email'], PDO::PARAM_STR);
    $stmt->execute();

    // Clear reset session data
    unset($_SESSION['fp_reset_code'], $_SESSION['fp_reset_email'], $_SESSION['fp_reset_expires'], $_SESSION['fp_verified']);

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Password changed successfully. Please sign in.'];
    header('Location: login.php');
    exit;
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Student Management System || Reset Password</title>
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script type="text/javascript">
    function valid() {
      var n = document.getElementById('newpassword').value;
      var c = document.getElementById('confirmpassword').value;
      if (n !== c) {
        if (window.showToast) showToast('New Password and Confirm Password Field do not match !!','warning');
        document.getElementById('confirmpassword').focus();
        return false;
      }
      if (n.length < 6) {
        if (window.showToast) showToast('Password must be at least 6 characters.','warning');
        document.getElementById('newpassword').focus();
        return false;
      }
      return true;
    }
  </script>
</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth">
        <div class="row flex-grow">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left p-5">
              <div class="brand-logo" align="center" style="font-weight:bold">Student Management System</div>
              <h6 class="font-weight-light">Set a new password for <strong><?php echo htmlentities($_SESSION['fp_reset_email']); ?></strong></h6>

              <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlentities($error); ?></div>
              <?php endif; ?>

              <form class="pt-3" method="post" onsubmit="return valid();">
                <div class="form-group">
                  <input id="newpassword" type="password" class="form-control form-control-lg" name="newpassword" placeholder="New Password" required autofocus>
                </div>
                <div class="form-group">
                  <input id="confirmpassword" type="password" class="form-control form-control-lg" name="confirmpassword" placeholder="Confirm Password" required>
                </div>
                <div class="mt-3">
                  <button class="btn btn-success btn-block loginbtn" type="submit">Change Password</button>
                </div>
                <div class="my-2 d-flex justify-content-between align-items-center">
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
