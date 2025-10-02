<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

function sendResetCode($email, $code) {
    $subject = "Your Password Reset Code";
    $message = "Your code is: $code";
    mail($email, $subject, $message); // Simplified version
}

if (isset($_POST['request_code'])) {
    $email = $_POST['email'];
    $code = rand(100000, 999999); // Generate a random 6-digit code

    // Check if email exists
    $sql = "SELECT Email FROM tbladmin WHERE Email=:email";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();
    
    if ($query->rowCount() > 0) {
        sendResetCode($email, $code);
        $_SESSION['reset_code'] = $code;
        $_SESSION['reset_email'] = $email;
        echo "<script>alert('A reset code has been sent to your email.');</script>";
    } else {
        echo "<script>alert('Email id is invalid');</script>";
    }
}

if (isset($_POST['reset_password'])) {
    $input_code = $_POST['input_code'];
    $newpassword = md5($_POST['newpassword']);

    if ($input_code == $_SESSION['reset_code'] && $_SESSION['reset_email'] == $_POST['email']) {
        $con = "UPDATE tbladmin SET Password=:newpassword WHERE Email=:email";
        $chngpwd1 = $dbh->prepare($con);
        $chngpwd1->bindParam(':email', $_SESSION['reset_email'], PDO::PARAM_STR);
        $chngpwd1->bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
        $chngpwd1->execute();
        echo "<script>alert('Your Password has been successfully changed');</script>";
        session_unset(); // Clear session
    } else {
        echo "<script>alert('Invalid reset code.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Profiling System | Forgot Password</title>
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style(v2).css"> <!-- Updated CSS file -->
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left p-5">
                            <div class="brand-logo" align="center">Student Management System</div>
                            <h4>RECOVER PASSWORD</h4>

                            <?php if (!isset($_SESSION['reset_code'])): ?>
                                <h6 class="font-weight-light">Enter your email address to receive a reset code!</h6>
                                <form class="pt-3" method="post" name="requestForm">
                                    <div class="form-group">
                                        <input type="email" class="form-control form-control-lg" placeholder="Email Address" required="true" name="email">
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-success btn-block" name="request_code" type="submit">Request Code</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <h6 class="font-weight-light">Enter the code sent to your email and your new password!</h6>
                                <form class="pt-3" method="post" name="resetForm">
                                    <div class="form-group">
                                        <input type="text" class="form-control form-control-lg" placeholder="Reset Code" required="true" name="input_code">
                                    </div>
                                    <div class="form-group">
                                        <input class="form-control form-control-lg" type="password" name="newpassword" placeholder="New Password" required="true" />
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-success btn-block" name="reset_password" type="submit">Reset Password</button>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <div class="my-2 d-flex justify-content-between align-items-center">
                                <a href="login.php" class="auth-link text-black">Sign In</a>
                            </div>
                            <div class="mb-2">
                                <a href="../index.php" class="btn btn-block btn-facebook auth-form-btn">
                                    <i class="icon-social-home mr-2"></i>Back Home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
</body>
</html>