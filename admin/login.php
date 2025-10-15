<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql = "SELECT ID, Password FROM tbladmin WHERE UserName=:username";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result && password_verify($password, $result->Password)) {
        // Check if the hash needs to be updated to a newer algorithm
        if (password_needs_rehash($result->Password, PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $rehashSql = "UPDATE tbladmin SET Password = :new_hash WHERE ID = :id";
            $rehashQuery = $dbh->prepare($rehashSql);
            $rehashQuery->bindParam(':new_hash', $newHash, PDO::PARAM_STR);
            $rehashQuery->bindParam(':id', $result->ID, PDO::PARAM_INT);
            $rehashQuery->execute();
        }
        $_SESSION['sturecmsaid'] = $result->ID;

        if (!empty($_POST["remember"])) {
            setcookie("user_login", $_POST["username"], time() + (10 * 365 * 24 * 60 * 60));
        } else {
            if (isset($_COOKIE["user_login"])) {
                setcookie("user_login", "");
            }
        }
        $_SESSION['login'] = $_POST['username'];
        echo "<script type='text/javascript'> document.location ='dashboard.php'; </script>";
    } else {
        echo "<script>alert('Invalid Details');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Student Profiling System | Login Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style(v2).css"> 
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left p-5">
                            <div class="brand-logo" align="center">Student Profiling System</div>
                            <h6 class="font-weight-light">Sign in to continue as Admin.</h6>
                            <form class="pt-3" id="login" method="post" name="login">
                                <div class="form-group">
                                    <input type="text" class="form-control form-control-lg"
                                        placeholder="Enter your username" required="true" name="username" value="<?php if (isset($_COOKIE["user_login"])) {
                                            echo $_COOKIE["user_login"];
                                        } ?>">
                                </div>
                                <div class="form-group" style="position: relative;">
                                    <input type="password" id="password" class="form-control form-control-lg"
                                        placeholder="Enter your password" name="password" required="true" value="">
                                    <i class="icon-eye" id="togglePassword"
                                        style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-success btn-block loginbtn" name="login"
                                        type="submit">Login</button>
                                </div>
                                <div class="my-2 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" id="remember" class="form-check-input"
                                                name="remember" <?php if (isset($_COOKIE["user_login"])) { ?> checked
                                                <?php } ?> /> Keep me signed in
                                        </label>
                                    </div>
                                    <a href="forgot-process.php" class="auth-link">Forgot password?</a>
                                </div>
                                <div class="mb-2">
                                    <a href="../index.php" class="btn btn-block btn-facebook auth-form-btn">
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
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/script.js"></script>
</body>

</html>