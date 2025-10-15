<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (isset($_POST['signup'])) {
    $stuid = $_POST['stuid'];
    $familyname = $_POST['familyname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    // Handle profile image upload
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
        echo "<script>if(window.showToast) showToast('Account already exists with this Student ID or Email','warning');</script>";
    } else {
        // Move uploaded file
        if ($profilepic && move_uploaded_file($profilepic_tmp, $profilepic_folder)) {
            $sql = "INSERT INTO tblstudent (StuID, FamilyName, FirstName, MiddleName, EmailAddress, Password, Image) 
                    VALUES (:stuid, :familyname, :firstname, :middlename, :email, :password, :image)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
            $query->bindParam(':familyname', $familyname, PDO::PARAM_STR);
            $query->bindParam(':firstname', $firstname, PDO::PARAM_STR);
            $query->bindParam(':middlename', $middlename, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':password', $password, PDO::PARAM_STR);
            $query->bindParam(':image', $profilepic, PDO::PARAM_STR);

            if ($query->execute()) {
                echo "<script>if(window.showToast) showToast('Account created successfully!','success'); document.location ='login.php';</script>";
            } else {
                echo "<script>if(window.showToast) showToast('Something went wrong. Please try again','danger');</script>";
            }
        } else {
            echo "<script>if(window.showToast) showToast('Profile picture upload failed. Please try again','danger');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Student Management System | Student Signup</title>
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <?php if (isset($error) && !empty($error)) { ?>
                <div id="login-toast" class="toast-box toast-show" style="z-index: 9999; background-color: #f44336;"><?= htmlspecialchars($error) ?></div>
            <?php } ?>
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow justify-content-center">
                    <div class="col-12 col-md-8 col-lg-5 mx-auto">
                        <div class="auth-form-light text-left p-4 p-md-5 rounded shadow-sm">
                            <div class="brand-logo text-center mb-3" style="font-weight:bold">
                                Student Management System
                            </div>
                            <h6 class="font-weight-light text-center mb-4">Create your account</h6>
                            <form id="signup" method="post" name="signup" enctype="multipart/form-data">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <input type="text" class="form-control" placeholder="Student ID" required
                                                name="stuid">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <input type="text" class="form-control" placeholder="Family Name" required
                                                name="familyname">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <input type="text" class="form-control" placeholder="First Name" required
                                                name="firstname">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <input type="text" class="form-control" placeholder="Middle Name"
                                                name="middlename">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <input type="email" class="form-control" placeholder="Email" required
                                                name="email">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-3" style="position: relative;">
                                            <input type="password" id="password" class="form-control" placeholder="Password" required
                                                name="password">
                                            <i class="icon-eye" id="togglePassword"
                                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label for="profilepic" class="form-label">Profile Picture</label>
                                            <input type="file" class="form-control" name="profilepic" id="profilepic"
                                                accept="image/*" required>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-success w-100 mb-3" name="signup" type="submit">Sign Up</button>
                                <div class="text-center mb-2">
                                    <a href="login.php" class="auth-link text-black">Already have an account? Login</a>
                                </div>
                                <div class="text-center">
                                    <a href="../index.php" class="btn btn-facebook auth-form-btn">
                                        <i class="icon-social-home mr-2"></i>Back Home
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="js/script.js"></script>
</body>

</html>