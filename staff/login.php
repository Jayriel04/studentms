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
    <title>Student Profiling System | Staff Login</title>
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style(v2).css"> <!-- Updated CSS file -->
    <script>
        // Minimal showToast fallback for pages that don't include header.php
        if (!window.showToast) {
            window.showToast = function (message, type) {
                type = type || 'info';
                try {
                    var container = document.getElementById('appToastContainer');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'appToastContainer';
                        container.setAttribute('aria-live', 'polite');
                        container.setAttribute('aria-atomic', 'true');
                        document.body.appendChild(container);
                        var s = document.createElement('style');
                        s.innerHTML = '#appToastContainer{position:fixed;top:1rem;right:1rem;z-index:1080} .app-toast{background:#fff;border-radius:6px;box-shadow:0 6px 18px rgba(0,0,0,0.08);padding:12px 16px;margin-bottom:8px;min-width:220px;display:flex;gap:8px;align-items:center}.app-toast.success{border-left:4px solid #28a745}.app-toast.info{border-left:4px solid #17a2b8}.app-toast.warning{border-left:4px solid #ffc107}.app-toast.danger{border-left:4px solid #dc3545}.app-toast .app-toast-close{margin-left:auto;cursor:pointer;color:#666}';
                        document.head.appendChild(s);
                    }
                    var toast = document.createElement('div');
                    toast.className = 'app-toast ' + (['info', 'success', 'warning', 'danger'].indexOf(type) === -1 ? 'info' : type);
                    toast.innerHTML = '<div class="app-toast-body">' + (message || '') + '</div>' +
                        '<div class="app-toast-close" role="button" aria-label="close">&times;</div>';
                    container.appendChild(toast);
                    toast.querySelector('.app-toast-close').addEventListener('click', function () { if (toast.parentNode) toast.parentNode.removeChild(toast); });
                    setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 3500);
                } catch (e) {
                    console && console.error && console.error(e);
                }
            };
        }
    </script>
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left p-5">
                            <div class="brand-logo" align="center">Student Profiling System - Staff</div>
                            <h6 class="font-weight-light">Sign in to continue as Staff.</h6>
                            <?php if (isset($login_error)): ?>
                                <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 50px;">
                                    <div class="toast" id="errorToast"
                                        style="position: absolute; top: 0; right: 0; min-width: 250px; z-index: 1050;"
                                        data-delay="3000" data-autohide="true">
                                        <div class="toast-header bg-danger text-white">
                                            <strong class="mr-auto">Login Failed</strong>
                                            <small>Now</small>
                                            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="toast-body">
                                            <?php echo $login_error; ?>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    window.addEventListener('DOMContentLoaded', function () {
                                        var toastEl = document.getElementById('errorToast');
                                        if (toastEl && window.$) {
                                            $(toastEl).toast('show');
                                        } else if (toastEl && typeof bootstrap !== "undefined") {
                                            var toast = new bootstrap.Toast(toastEl);
                                            toast.show();
                                        }
                                    });
                                </script>
                            <?php endif; ?>
                            <form class="pt-3" id="login" method="post" name="login">
                                <div class="form-group">
                                    <input type="text" class="form-control form-control-lg"
                                        placeholder="Enter your username" required name="username"
                                        value="<?php if (isset($_COOKIE['user_login_staff'])) {
                                            echo $_COOKIE['user_login_staff'];
                                        } ?>">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control form-control-lg"
                                        placeholder="Enter your password" name="password" required
                                        value="">
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-success btn-block loginbtn" name="login"
                                        type="submit">Login</button>
                                </div>
                                <div class="my-2 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" id="remember" class="form-check-input"
                                                name="remember" <?php if (isset($_COOKIE["user_login_staff"])) { ?>
                                                    checked <?php } ?> /> Keep me signed in
                                        </label>
                                    </div>
                                    <a href="forgot-process.php" class="auth-link text-black">Forgot password?</a>
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
</body>

</html>