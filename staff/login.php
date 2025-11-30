<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; // Use plaintext password for verification
    // include Status to ensure inactive users cannot login
    $sql = "SELECT ID, Status, Password FROM tblstaff WHERE UserName=:username";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    if ($result && password_verify($password, $result->Password)) {
        // if Status is present and is active (1) allow login, otherwise block
        $status = isset($result->Status) ? intval($result->Status) : 1;

        // Check if the hash needs to be updated to a newer algorithm
        if (password_needs_rehash($result->Password, PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $rehashSql = "UPDATE tblstaff SET Password = :new_hash WHERE ID = :id";
            $rehashQuery = $dbh->prepare($rehashSql);
            $rehashQuery->bindParam(':new_hash', $newHash, PDO::PARAM_STR);
            $rehashQuery->bindParam(':id', $result->ID, PDO::PARAM_INT);
            $rehashQuery->execute();
        }

        if ($status === 1) {
            $_SESSION['sturecmsstaffid'] = $result->ID;
            if (!empty($_POST["remember"])) { // Secure "Remember Me" - only store username
                setcookie("user_login_staff", $_POST["username"], time() + (10 * 365 * 24 * 60 * 60));
            } else if (isset($_COOKIE["user_login_staff"])) {
                setcookie("user_login_staff", "", time() - 3600); // Expire cookie
            }
            $_SESSION['login_staff'] = $_POST['username'];
            echo "<script type='text/javascript'> document.location ='dashboard.php'; </script>";
        } else {
            // account found but inactive
            $login_error = 'Your account is inactive. Please contact the administrator.';
        }
    } else {
        $login_error = 'Invalid Details';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Student Profiling System</title>
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="css/login-new.css">
</head>

<body>
    <div class="container">
        <div class="welcome-section">
            <div class="welcome-content">
                <h1>WELCOME STAFF</h1>
                <p class="headline">Student Profiling System</p>
                <p>Sign in to access student records, manage class information, and utilize other staff-specific tools
                    and resources.</p>
            </div>
            <div class="circle-decoration"></div>
        </div>

        <div class="form-section">
            <h2>Sign in</h2>
            <p class="subtitle">Enter your credentials to access the staff dashboard.</p>

            <?php if (isset($login_error)): ?>
                <div class="alert alert-danger"
                    style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;">
                    <?php echo $login_error; ?></div>
            <?php endif; ?>

            <form id="login" method="post" name="login">
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">👤</span>
                        <input type="text" id="username" name="username" placeholder="User Name" required="true" value="<?php if (isset($_COOKIE['user_login_staff'])) {
                            echo $_COOKIE['user_login_staff'];
                        } ?>">
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">🔒</span>
                        <input type="password" id="password" name="password" placeholder="Password" required="true">
                        <button type="button" class="toggle-password" onclick="togglePassword()">SHOW</button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" id="remember" name="remember" <?php if (isset($_COOKIE["user_login_staff"])) { ?> checked <?php } ?>>
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-process.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" name="login" class="btn btn-primary">Sign in</button>
                <a href="../index.php" class="btn btn-secondary">Back to Home</a>

            </form>
        </div>
    </div>

    <script src="js/login-new.js"></script>
    <script src="js/toast.js"></script>
</body>

</html>