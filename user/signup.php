<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (isset($_POST['signup'])) {
    $message = '';
    $stuid = $_POST['stuid'];
    $familyname = $_POST['familyname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    // Handle profile image upload - this part has a potential security issue with file names.
    $profilepic = $_FILES['profilepic']['name'];
    $profilepic_tmp = $_FILES['profilepic']['tmp_name'];
    $profilepic_folder = "../admin/images/" . $profilepic;

    // Check if student already exists
    $sql = "SELECT ID FROM tblstudent WHERE StuID=:stuid OR EmailAddress=:email";
    $query = $dbh->prepare($sql);
    $query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() > 0) {
        $message = 'An account already exists with this Student ID or Email.';
        $message_type = 'danger';
    } else {
        // Move uploaded file
        if ($profilepic && move_uploaded_file($profilepic_tmp, $profilepic_folder)) {
            $sql = "INSERT INTO tblstudent (StuID, FamilyName, FirstName, MiddleName, EmailAddress, Password, Image) 
                    VALUES (:stuid, :familyname, :firstname, :middlename, :email, :password, :image)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':stuid', $stuid);
            $query->bindParam(':familyname', $familyname);
            $query->bindParam(':firstname', $firstname);
            $query->bindParam(':middlename', $middlename);
            $query->bindParam(':email', $email);
            $query->bindParam(':password', $password);
            $query->bindParam(':image', $profilepic);

            if ($query->execute()) {
                $_SESSION['flash_message'] = 'Account created successfully! You can now log in.';
                header('Location: login.php');
                exit;
            } else {
                $message = 'Something went wrong. Please try again.';
                $message_type = 'danger';
            }
        } else {
            $message = 'Profile picture upload failed. Please try again.';
            $message_type = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sign Up | Student Profiling System</title>
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="css/login-new.css">
</head>
<body>
    <div class="container">
        <div class="welcome-section">
            <div class="welcome-content">
                <h1>JOIN US</h1>
                <p class="headline">Student Profiling System</p>
                <p>Create your account to get started. Access your profile, grades, and class information all in one place.</p>
            </div>
            <div class="circle-decoration"></div>
        </div>

        <div class="form-section">
            <h2>Create Account</h2>
            <p class="subtitle">Fill out the form below to register.</p>

            <?php if (!empty($message)): ?>
                <div class="alert alert-danger" style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form id="signup" method="post" name="signup" enctype="multipart/form-data">
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">🆔</span>
                        <input type="text" name="stuid" placeholder="Student ID" required="true">
                    </div>
                </div>
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">👤</span>
                        <input type="text" name="familyname" placeholder="Family Name" required="true">
                    </div>
                </div>
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">👤</span>
                        <input type="text" name="firstname" placeholder="First Name" required="true">
                    </div>
                </div>
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">👤</span>
                        <input type="text" name="middlename" placeholder="Middle Name">
                    </div>
                </div>
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">✉️</span>
                        <input type="email" name="email" placeholder="Email" required="true">
                    </div>
                </div>
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">🔒</span>
                        <input type="password" id="password" name="password" placeholder="Password" required="true">
                        <button type="button" class="toggle-password" onclick="togglePassword()">SHOW</button>
                    </div>
                </div>
                <div class="input-group">
                    <label for="profilepic" style="font-weight: 500; font-size: 13px; color: #333;">Profile Picture</label>
                    <input type="file" name="profilepic" id="profilepic" class="form-control" accept="image/*" required style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; background: #f8f8f8;">
                </div>

                <button class="btn btn-primary" name="signup" type="submit">Sign Up</button>
                <a href="../index.php" class="btn btn-secondary">Back to Home</a>

                <div class="signup-link">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>
            </form>
        </div>
    </div>
    <script src="js/login-new.js"></script>
</body>
</html>