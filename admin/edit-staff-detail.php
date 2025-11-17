<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
    header('location:logout.php');
} else {
    $eid = $_GET['editid'];
    $success_message = '';
    $error_message = '';

    // Handle update logic first
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Check for duplicate username
        $checkSql = "SELECT ID FROM tblstaff WHERE UserName = :username AND ID != :eid";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery->bindParam(':username', $username, PDO::PARAM_STR);
        $checkQuery->bindParam(':eid', $eid, PDO::PARAM_INT);
        $checkQuery->execute();

        if ($checkQuery->rowCount() > 0) {
            $error_message = "Username already exists. Please choose a different one.";
        } else {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE tblstaff SET StaffName=:name, UserName=:username, Email=:email, Password=:password WHERE ID=:eid";
            } else {
                $sql = "UPDATE tblstaff SET StaffName=:name, UserName=:username, Email=:email WHERE ID=:eid";
            }
            $query = $dbh->prepare($sql);
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':eid', $eid, PDO::PARAM_INT);
            if (!empty($password)) {
                $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            }
            $query->execute();
            $success_message = "Staff record has been updated successfully.";
        }
    }
    // Fetch for prefill
    $sql = "SELECT * FROM tblstaff WHERE ID=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Student Profiling System || Edit Staff</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
        <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
        <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
        <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
        <link rel="stylesheet" href="./css/style.css">
        <link rel="stylesheet" href="./css/style(v2).css">
    </head>

    <body>
        <div class="container-scroller">
            <?php include_once('includes/header.php'); ?>
            <div class="container-fluid page-body-wrapper">
                <?php include_once('includes/sidebar.php'); ?>
                <div class="main-panel">
                    <div class="content-wrapper">
                        <div class="page-header">
                            <h3 class="page-title">Update Staff</h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Update Staff Details</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="row">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title" style="text-align: center;">Update Staff Details</h4>
                                        <hr />
                                        <?php if (!empty($success_message)): ?>
                                            <div class="alert alert-success">
                                                <?php echo htmlentities($success_message); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($error_message)): ?>
                                            <div class="alert alert-danger">
                                                <?php echo htmlentities($error_message); ?>
                                            </div>
                                        <?php endif; ?>
                                        <form class="forms-sample" method="post">
                                            <div class="form-group">
                                                <label>ID</label>
                                                <input type="text" value="<?php echo htmlentities($result->ID); ?>"
                                                    class="form-control" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label>Name</label>
                                                <input type="text" name="name"
                                                    value="<?php echo htmlentities($result->StaffName); ?>" class="form-control"
                                                    required style="text-transform: capitalize;">
                                            </div>
                                            <div class="form-group">
                                                <label>User Name</label>
                                                <input type="text" name="username"
                                                    value="<?php echo htmlentities($result->UserName); ?>" class="form-control"
                                                    required>
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="email"
                                                    value="<?php echo htmlentities($result->Email); ?>" class="form-control"
                                                    required>
                                            </div>
                                            <div class="form-group" style="position: relative;">
                                                <label>Change Password</label>
                                                <input type="password" id="password" name="password" class="form-control"
                                                    placeholder="Leave blank to keep unchanged">
                                                <i class="icon-eye" id="togglePassword"
                                                   style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                                            </div>
                                            <div class="form-group">
                                                <label>StaffRegdate</label>
                                                <input type="text"
                                                    value="<?php echo htmlentities($result->StaffRegdate); ?>"
                                                    class="form-control" readonly>
                                            </div>
                                            <button type="submit" class="btn btn-primary mr-2" name="submit">Update</button>
                                            <a href="manage-staff.php" class="btn btn-light">Back</a>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php include_once('includes/footer.php'); ?>
                </div>
            </div>
        </div>
        <script src="vendors/js/vendor.bundle.base.js"></script>
        <script src="js/off-canvas.js"></script>
        <script src="js/misc.js"></script>
        <script>
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');

            if (togglePassword && password) {
                togglePassword.addEventListener('click', function (e) {
                    // toggle the type attribute
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    // toggle the eye slash icon
                    this.classList.toggle('icon-eye-off');
                });
            }
        </script>
    </body>

    </html>
<?php } ?>