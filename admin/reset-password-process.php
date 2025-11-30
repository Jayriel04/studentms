<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../includes/dbconnection.php';

// Config: adjust if admin schema differs
$ADMIN_TABLE = 'tbladmin';
$ADMIN_EMAIL_COLUMN = 'Email';
$ADMIN_PASSWORD_COLUMN = 'Password';

// Ensure verification step passed
if (empty($_SESSION['admin_fp_verified']) || $_SESSION['admin_fp_verified'] !== true || empty($_SESSION['admin_fp_reset_email'])) {
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
    $sql = "UPDATE {$ADMIN_TABLE} SET {$ADMIN_PASSWORD_COLUMN} = :p WHERE {$ADMIN_EMAIL_COLUMN} = :e";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':p', $hash, PDO::PARAM_STR);
    $stmt->bindParam(':e', $_SESSION['admin_fp_reset_email'], PDO::PARAM_STR);
    $stmt->execute();

    // Clear reset session data
    unset($_SESSION['admin_fp_reset_code'], $_SESSION['admin_fp_reset_email'], $_SESSION['admin_fp_reset_expires'], $_SESSION['admin_fp_verified']);

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Password changed successfully. Please sign in.'];
    header('Location: login.php');
    exit;
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password | Student Profiling System</title>
  <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
  <link rel="stylesheet" href="css/login-new.css">
  <script type="text/javascript">
    function valid() {
      var n = document.getElementById('newpassword').value;
      var c = document.getElementById('confirmpassword').value;
      if (n !== c) {
        if (window.showToast) showToast('New Password and Confirm Password Field do not match !!', 'warning');
        document.getElementById('confirmpassword').focus();
        return false;
      }
      if (n.length < 6) {
        if (window.showToast) showToast('Password must be at least 6 characters.', 'warning');
        document.getElementById('newpassword').focus();
        return false;
      }
      return true;
    }
  </script>
</head>

<body>
  <div class="container">
    <div class="welcome-section">
      <div class="welcome-content">
        <h1>SET NEW PASSWORD</h1>
        <p class="headline">Student Profiling System</p>
        <p>Create a new, secure password for your account. Make sure it's strong and something you can remember.</p>
      </div>
      <div class="circle-decoration"></div>
    </div>

    <div class="form-section">
      <h2>Reset Your Password</h2>
      <p class="subtitle">Set a new password for
        <strong><?php echo htmlentities($_SESSION['admin_fp_reset_email']); ?></strong>.
      </p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"
          style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;">
          <?php echo htmlentities($error); ?></div>
      <?php endif; ?>

      <form method="post" onsubmit="return valid();">
        <div class="input-group">
          <div class="input-wrapper">
            <span class="icon">🔒</span>
            <input id="newpassword" type="password" name="newpassword" placeholder="New Password" required autofocus>
            <button type="button" class="toggle-password"
              onclick="togglePasswordVisibility(this, 'newpassword')">SHOW</button>
          </div>
        </div>
        <div class="input-group">
          <div class="input-wrapper">
            <span class="icon">🔒</span>
            <input id="confirmpassword" type="password" name="confirmpassword" placeholder="Confirm Password" required>
            <button type="button" class="toggle-password"
              onclick="togglePasswordVisibility(this, 'confirmpassword')">SHOW</button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Change Password</button>
        <a href="login.php" class="btn btn-secondary">Back to Sign In</a>

      </form>
    </div>
  </div>

  <script src="js/auth-forms.js"></script>
  <script src="js/toast.js"></script>
  <script src="js/script.js"></script>
</body>

</html>