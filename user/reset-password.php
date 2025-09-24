<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Ensure previous verification step was completed (we still rely on session reset_email)
if (empty($_SESSION['reset_email'])) {
  header('Location: forgot-password.php');
  exit;
}

if (isset($_POST['reset_password'])) {
  $email = trim($_SESSION['reset_email']);
  $newpassword = $_POST['newpassword'];

  if (time() > $_SESSION['reset_expires']) {
    unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
    $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Reset code expired. Please request a new code.'];
    header('Location: forgot-password.php');
    exit;
  }

  if (strlen($newpassword) < 6) {
    $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Password must be at least 6 characters.'];
    header('Location: reset-password.php');
    exit;
  } else {
    $hash = password_hash($newpassword, PASSWORD_DEFAULT);
    $sql = "UPDATE tblstudent SET Password=:newpassword WHERE EmailAddress=:email";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':newpassword', $hash, PDO::PARAM_STR);
    $query->execute();

    unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Your password has been changed successfully. Please sign in.'];
    header('Location: login.php');
    exit;
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Reset Password</title>
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="css/style.css">
  <script type="text/javascript">
    function valid() {
      if (document.chngpwd.newpassword.value != document.chngpwd.confirmpassword.value) {
        alert("New Password and Confirm Password Field do not match  !!");
        document.chngpwd.confirmpassword.focus();
        return false;
      }
      return true;
    }
  </script>
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
                  <h4 class="card-title text-center">Set New Password</h4>
                  <p class="text-muted text-center">for <strong><?php echo htmlentities($_SESSION['reset_email']);?></strong></p>
                  <?php if (!empty($_SESSION['flash'])): ?>
                    <div class="alert alert-<?php echo htmlentities($_SESSION['flash']['type']); ?>" role="alert">
                      <?php echo htmlentities($_SESSION['flash']['msg']); ?>
                    </div>
                    <?php unset($_SESSION['flash']); ?>
                  <?php endif; ?>
                  <form class="pt-3" method="post" name="chngpwd" onsubmit="return valid();" novalidate>
                    <div class="form-group">
                      <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="icon-lock"></i></span></div>
                        <input class="form-control form-control-lg" type="password" name="newpassword" placeholder="New Password" required="true" autofocus />
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="icon-key"></i></span></div>
                        <input class="form-control form-control-lg" type="password" name="confirmpassword" placeholder="Confirm Password" required="true" />
                      </div>
                    </div>
                    <div class="mt-3">
                      <button class="btn btn-success btn-block loginbtn" name="reset_password" type="submit">Change Password</button>
                    </div>
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
