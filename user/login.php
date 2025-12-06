<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (isset($_POST['login'])) {
  $stuid = $_POST['stuid'];
  $password = $_POST['password'];

  // Server-side validation for student ID format with optional spaces
  if (!preg_match('/^\d{3}\s*-\s*\d{5}$/', $stuid)) {
    $error = 'Invalid Student ID format. Please use the format: ###-#####.';
  } else {


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
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In | Student Profiling System</title>
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
        <h1>WELCOME STUDENT</h1>
        <p class="headline">Student Profiling System</p>
        <p>Sign in to view your academic records, check class schedules, and access your personal profile and other
          student resources.</p>
      </div>
      <div class="circle-decoration"></div>
    </div>

    <div class="form-section">
      <h2>Sign in</h2>
      <p class="subtitle">Enter your credentials to access the student portal.</p>

      <?php if (isset($error) && !empty($error)): ?>
        <div class="alert alert-danger"
          style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form id="login" method="post" name="login">
        <div class="input-group">
          <div class="input-wrapper">
            <span class="icon">🆔</span>
            <input type="text" name="stuid" placeholder="e.g., 123-45678" required="true" pattern="\d{3}\s*-\s*\d{5}"
              title="The format must be: ###-#####" value="<?php if (isset($_COOKIE["user_login"])) {
                echo $_COOKIE["user_login"];
              } ?>">
          </div>
        </div>

        <div class="input-group">
          <div class="input-wrapper">
            <span class="icon">🔒</span>
            <input type="password" id="password" name="password" placeholder="Enter your Password" required="true"
              value="<?php if (isset($_COOKIE["userpassword"])) {
                echo $_COOKIE["userpassword"];
              } ?>">
            <button type="button" class="toggle-password" onclick="togglePassword()">SHOW</button>
          </div>
        </div>

        <div class="form-options">
          <label class="remember-me">
            <input type="checkbox" id="remember" name="remember" <?php if (isset($_COOKIE["user_login"])) { ?> checked
              <?php } ?>>
            <span>Remember me</span>
          </label>
          <a href="forgot-process.php" class="forgot-password">Forgot Password?</a>
        </div>

        <button type="submit" name="login" class="btn btn-primary">Sign in</button>
        <a href="../index.php" class="btn btn-secondary">Back to Home</a>

        <div class="signup-link">
          Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
      </form>
    </div>
  </div>
  <script src="js/login-new.js"></script>
  <script src="js/toast.js"></script>
</body>

</html>