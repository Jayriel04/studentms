<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');

if (isset($_POST['signup'])) {
    $message = '';
    $message_type = 'danger';
    $stuid = $_POST['stuid'] ?? '';

    // Server-side validation for student ID format
    if (!preg_match('/^\d{3}\s*-\s*\d{5}$/', $stuid)) {
        $message = 'Invalid Student ID format. Please use the format: ###-#####.';
        $message_type = 'danger';
    } else {
        $familyname = $_POST['familyname'] ?? '';
        $firstname = $_POST['firstname'] ?? '';
        $middlename = $_POST['middlename'] ?? '';
        $email = $_POST['email'] ?? '';

        // Validate required fields
        if (empty($familyname) || empty($firstname) || empty($email) || empty($_POST['password'])) { // Add this check
            $message = 'Please fill in all required fields.';
            $message_type = 'danger';
        } elseif (strlen($_POST['password']) < 5) {
            $message = 'Password must be at least 5 characters long.';
            $message_type = 'danger';
        } else {
            // Use modern password hashing instead of md5
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

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
                $sanitized_pic_name = null; // Default to no image

                // Handle optional profile picture upload
                if (!empty($_FILES['profilepic']['name'])) {
                    $profilepic_name = basename($_FILES['profilepic']['name']);
                    $sanitized_pic_name = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $profilepic_name);

                    $images_dir = "../admin/images/";
                    if (!is_dir($images_dir)) {
                        mkdir($images_dir, 0755, true);
                    }
                    $profilepic_folder = $images_dir . $sanitized_pic_name;

                    if (!move_uploaded_file($_FILES['profilepic']['tmp_name'], $profilepic_folder)) {
                        $message = 'Profile picture upload failed. Please try again.';
                        $message_type = 'danger';
                        $sanitized_pic_name = null; // Ensure it's null on failure
                    }
                }

                // Proceed if there was no upload error
                if (empty($message)) {
                    $sql = "INSERT INTO tblstudent (StuID, FamilyName, FirstName, MiddleName, EmailAddress, Password, Image) 
                            VALUES (:stuid, :familyname, :firstname, :middlename, :email, :password, :image)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
                    $query->bindParam(':familyname', $familyname, PDO::PARAM_STR);
                    $query->bindParam(':firstname', $firstname, PDO::PARAM_STR);
                    $query->bindParam(':middlename', $middlename, PDO::PARAM_STR); // This was missing
                    $query->bindParam(':email', $email, PDO::PARAM_STR);
                    $query->bindParam(':password', $password, PDO::PARAM_STR);
                    $query->bindParam(':image', $sanitized_pic_name, PDO::PARAM_STR);

                    if ($query->execute()) {
                        $lastInsertId = $dbh->lastInsertId();
                        $_SESSION['sturecmsstuid'] = $stuid;
                        $_SESSION['sturecmsuid'] = $lastInsertId;
                        $_SESSION['login'] = $stuid;
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        $message = 'Database error: ' . $query->errorInfo()[2];
                        $message_type = 'danger';
                    }
                }
            }
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
                <p>Create your account to get started. Access your profile, grades, and class information all in one
                    place.</p>
            </div>
            <div class="circle-decoration"></div>
        </div>

        <div class="form-section">
            <h2>Create Account</h2>
            <p class="subtitle">Fill out the form below to register.</p>

            <?php if (!empty($message)): ?>
                <div class="alert alert-danger"
                    style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;">
                    <?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form id="signup" method="post" name="signup" enctype="multipart/form-data">
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">🆔</span>
                        <input type="text" name="stuid" placeholder="e.g., 123-45678" required="true"
                            pattern="\d{3}\s*-\s*\d{5}" title="The format must be: ###-#####">
                    </div>
                </div>
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">👤</span>
                        <input type="text" name="familyname" placeholder="Family Name" required="true"
                            style="text-transform: capitalize;">
                    </div>
                </div>
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">👤</span>
                        <input type="text" name="firstname" placeholder="First Name" required="true"
                            style="text-transform: capitalize;">
                    </div>
                </div>
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">👤</span>
                        <input type="text" name="middlename" placeholder="Middle Name"
                            style="text-transform: capitalize;">
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
                    <div id="password-strength" style="margin-top: 5px; font-size: 12px; text-align: left;"></div>
                </div>
                <div class="input-group">
                    <label for="profilepic" style="font-weight: 500; font-size: 13px; color: #333;">Profile
                        Picture</label>
                    <input type="file" name="profilepic" id="profilepic" class="form-control" accept="image/*"
                        style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; background: #f8f8f8;">
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
    <script src="js/toast.js"></script>
  </body>

</html>