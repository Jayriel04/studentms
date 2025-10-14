<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (isset($_POST['login'])) {
  $stuid = $_POST['stuid'];
  $password = $_POST['password'];

  // Fetch stored password and status for this student
  $sql = "SELECT StuID, ID, Password, Status FROM tblstudent WHERE StuID=:stuid";
  $query = $dbh->prepare($sql);
  $query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
  $query->execute();
  $result = $query->fetch(PDO::FETCH_OBJ);

  $authenticated = false;
  if ($result) {
    $stored = $result->Password;
    // Prefer password_verify for modern hashes
    if (password_verify($password, $stored)) {
      $authenticated = true;
    } else {
      // Fallback for legacy md5 hashes
      if (md5($password) === $stored) {
        $authenticated = true;
        // Upgrade to stronger hash
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $dbh->prepare("UPDATE tblstudent SET Password=:p WHERE StuID=:stuid");
        $upd->bindParam(':p', $newHash, PDO::PARAM_STR);
        $upd->bindParam(':stuid', $stuid, PDO::PARAM_STR);
        $upd->execute();
      }
    }
  }

  if ($authenticated) {
    // Check account status: 1 => active, 0 => inactive
    if (isset($result->Status) && intval($result->Status) !== 1) {
      // Don't log in; show inactive-account toast
      $error = 'Your account is inactive. Please contact the administrator.';
    } else {
      $_SESSION['sturecmsstuid'] = $result->StuID;
      $_SESSION['sturecmsuid'] = $result->ID;

      if (!empty($_POST["remember"])) {
        setcookie("user_login", $_POST["stuid"], time() + (10 * 365 * 24 * 60 * 60));
        setcookie("userpassword", $_POST["password"], time() + (10 * 365 * 24 * 60 * 60));
      } else {
        if (isset($_COOKIE["user_login"])) {
          setcookie("user_login", "");
        }
        if (isset($_COOKIE["userpassword"])) {
          setcookie("userpassword", "");
        }
      }

      $_SESSION['login'] = $_POST['stuid'];
      echo "<script type='text/javascript'> document.location ='dashboard.php'; </script>";
    }
  } else {
    // Use a non-blocking toast message instead of alert()
    $error = 'Invalid Student ID or Password';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Student Management System || Student Login Page</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- plugins:css -->
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <!-- Layout styles -->
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Simple toast styles */
    .toast-box {
      position: fixed;
      right: 20px;
      top: 20px;
      background: rgba(0, 0, 0, 0.85);
      color: #fff;
      padding: 12px 16px;
      border-radius: 6px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
      z-index: 9999;
      display: flex;
      align-items: center;
      gap: 12px;
      max-width: 320px;
      font-family: sans-serif;
    }

    .toast-box button {
      background: transparent;
      border: none;
      color: #fff;
      font-size: 18px;
      line-height: 1;
      cursor: pointer;
    }

    .toast-show {
      animation: toast-in 0.2s ease-out;
    }

    @keyframes toast-in {
      from {
        transform: translateY(-8px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
  </style>
</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth">
        <div class="row flex-grow">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left p-5">
              <div class="brand-logo" align="center" style="font-weight:bold">
                Student Management System
              </div>
              <h6 class="font-weight-light">Sign in to continue as Student.</h6>
              <form class="pt-3" id="login" method="post" name="login">
                <div class="form-group">
                  <input type="text" class="form-control form-control-lg" placeholder="Enter your Student ID"
                    required="true" name="stuid"
                    value="<?php if (isset($_COOKIE["user_login"])) {
                      echo $_COOKIE["user_login"];
                    } ?>">
                </div>
                <div class="form-group" style="position: relative;">
                  <input type="password" id="password" class="form-control form-control-lg" placeholder="Enter your Password"
                    name="password" required="true" 
                    value="<?php if (isset($_COOKIE["userpassword"])) {
                      echo $_COOKIE["userpassword"];
                    } ?>">
                    <i class="icon-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                </div>
                <div class="mt-3">
                  <button class="btn btn-success btn-block loginbtn" name="login" type="submit">Login</button>
                </div>
                <div class="my-2 d-flex justify-content-between align-items-center">
                  <div class="form-check">
                    <label class="form-check-label text-muted">
                      <input type="checkbox" id="remember" class="form-check-input" name="remember" <?php if (isset($_COOKIE["user_login"])) { ?> checked <?php } ?> /> Keep me signed in </label>
                  </div>
                  <a href="forgot-process.php" class="auth-link text-black">Forgot password?</a>
                </div>
                <div class="my-2 d-flex justify-content-between align-items-center">
                  <a href="signup.php" class="auth-link text-black">Don't have an account? Sign Up</a>
                </div>
                <div class="mb-2">
                  <a href="../index.php" class="btn btn-block btn-facebook auth-form-btn">
                    <i class="icon-social-home mr-2"></i>Back Home </a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- content-wrapper ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->
  <!-- plugins:js -->
  <script src="vendors/js/vendor.bundle.base.js"></script>
  <!-- inject:js -->
  <script src="js/off-canvas.js"></script>
  <script src="js/misc.js"></script>
  <script src="js/script.js"></script>
  <?php if (isset($error) && !empty($error)) { ?>
    <div id="login-toast" class="toast-box toast-show">
      <div class="toast-message"><?= htmlspecialchars($error) ?></div>
      <button id="login-toast-close" aria-label="Close">&times;</button>
    </div>
    <script>
      (function () {
        var toast = document.getElementById('login-toast');
        var close = document.getElementById('login-toast-close');
        function hide() { if (!toast) return; toast.style.transition = 'opacity 0.25s ease'; toast.style.opacity = '0'; setTimeout(function () { if (toast && toast.parentNode) toast.parentNode.removeChild(toast); }, 300); }
        // Auto-hide after 4 seconds
        setTimeout(hide, 4000);
        // Close on click
        close && close.addEventListener('click', hide);
      })();
    </script>
  <?php } ?>
</body>

</html>