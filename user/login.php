<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (isset($_POST['login'])) {
  $stuid = $_POST['stuid'];
  $password = $_POST['password'];

  // Fetch stored password for this student
  $sql = "SELECT StuID, ID, Password FROM tblstudent WHERE StuID=:stuid";
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
  } else {
    echo "<script>alert('Invalid Student ID or Password');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Student Management System || Student Login Page</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <!-- Layout styles -->
  <link rel="stylesheet" href="css/style.css">
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
                  <input type="text" class="form-control form-control-lg" placeholder="Enter your Student ID" required="true" name="stuid" value="<?php if (isset($_COOKIE["user_login"])) {
                                                                                                                                                  echo $_COOKIE["user_login"];
                                                                                                                                                } ?>">
                </div>
                <div class="form-group">
                  <input type="password" class="form-control form-control-lg" placeholder="Enter your Password" name="password" required="true" value="<?php if (isset($_COOKIE["userpassword"])) {
                                                                                                                                                      echo $_COOKIE["userpassword"];
                                                                                                                                                    } ?>">
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
</body>

</html>