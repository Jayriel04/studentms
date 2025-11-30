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
    } else if (strlen($new) < 5) {
        $error = 'Password must be at least 5 characters long.';
    } else {
        // Fetch student details before updating password
        $email = $_SESSION['fp_reset_email'];
        $sql_select = "SELECT ID, StuID FROM tblstudent WHERE EmailAddress = :email";
        $query_select = $dbh->prepare($sql_select);
        $query_select->bindParam(':email', $email, PDO::PARAM_STR);
        $query_select->execute();
        $student = $query_select->fetch(PDO::FETCH_OBJ);

        if ($student) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $sql_update = "UPDATE tblstudent SET Password = :p WHERE EmailAddress = :e";
            $stmt_update = $dbh->prepare($sql_update);
            $stmt_update->bindParam(':p', $hash, PDO::PARAM_STR);
            $stmt_update->bindParam(':e', $email, PDO::PARAM_STR);
            $stmt_update->execute();

            // Clear reset session data
            unset($_SESSION['fp_reset_code'], $_SESSION['fp_reset_email'], $_SESSION['fp_reset_expires'], $_SESSION['fp_verified']);

            // Automatically log the user in
            $_SESSION['sturecmsstuid'] = $student->StuID;
            $_SESSION['sturecmsuid'] = $student->ID;
            $_SESSION['login'] = $student->StuID;
            header('Location: dashboard.php');
            exit;
        } else {
            // This case is unlikely if they got this far, but good for safety
            $error = 'An unexpected error occurred. Please try again.';
        }
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
</head>

<body>
    <div class="container">
        <div class="welcome-section">
            <div class="welcome-content">
                <h1>SET NEW PASSWORD</h1>
                <p class="headline">Student Profiling System</p>
                <p>Create a new, secure password for your account. Make sure it's strong and something you can remember.
                </p>
            </div>
            <div class="circle-decoration"></div>
        </div>

        <div class="form-section">
            <h2>Reset Your Password</h2>
            <p class="subtitle">Set a new password for
                <strong><?php echo htmlentities($_SESSION['fp_reset_email']); ?></strong>.
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"
                    style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;">
                    <?php echo htmlentities($error); ?></div>
            <?php endif; ?>

            <form id="resetPasswordForm" method="post">
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">🔒</span>
                        <input id="newpassword" type="password" name="newpassword" placeholder="New Password" required
                            autofocus>
                        <button type="button" class="toggle-password"
                            onclick="togglePasswordVisibility(this, 'newpassword')">SHOW</button>
                    </div>
                    <div id="password-strength"
                        style="margin-top: 5px; font-size: 12px; text-align: left; width: 100%;"></div>
                </div>
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">🔒</span>
                        <input id="confirmpassword" type="password" name="confirmpassword"
                            placeholder="Confirm Password" required>
                        <button type="button" class="toggle-password"
                            onclick="togglePasswordVisibility(this, 'confirmpassword')">SHOW</button>
                    </div>
                    <div id="password-match-error"
                        style="margin-top: 5px; font-size: 12px; text-align: left; width: 100%; color: red;"></div>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Change Password</button>
                <a href="login.php" class="btn btn-secondary">Back to Sign In</a>
            </form>
        </div>
    </div>

    <script src="js/auth-forms.js"></script>
    <script src="js/toast.js"></script>
  </body>

  </html>