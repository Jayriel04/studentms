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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sign In | Student Profiling System</title>
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="css/login-new.css">
</head>

<body>
    <div class="container">
        <div class="welcome-section">
            <div class="welcome-content">
                <h1>WELCOME ADMIN</h1>
                <p class="headline">Student Profiling System</p>
                <p>Sign in to manage student data, view profiles, and access administrative tools. Your role is crucial for maintaining an organized and efficient system.</p>
            </div>
            <div class="circle-decoration"></div>
        </div>

        <div class="form-section">
            <h2>Sign in</h2>
            <p class="subtitle">Enter your credentials to access the admin dashboard.</p>

            <form id="login" method="post" name="login">
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">👤</span>
                        <input type="text" id="username" name="username" placeholder="User Name" required="true" value="<?php if (isset($_COOKIE["user_login"])) {
                            echo $_COOKIE["user_login"];
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
                        <input type="checkbox" id="remember" name="remember" <?php if (isset($_COOKIE["user_login"])) { ?> checked <?php } ?>>
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
</body>

</html>