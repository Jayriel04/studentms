<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
    header('location:logout.php');
} else {
    $eid = $_GET['editid'];
    // Handle update logic first
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        if (!empty($password)) {
            $password = md5($password);
            $sql = "UPDATE tblstaff SET StaffName=:name, UserName=:username, Email=:email, Password=:password WHERE ID=:eid";
        } else {
            $sql = "UPDATE tblstaff SET StaffName=:name, UserName=:username, Email=:email WHERE ID=:eid";
        }
        $query = $dbh->prepare($sql);
        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':eid', $eid, PDO::PARAM_STR);
        if (!empty($password)) {
            $query->bindParam(':password', $password, PDO::PARAM_STR);
        }
        $query->execute();
        echo '<script>alert("Staff record has been updated")</script>';
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
    <title>Edit Staff</title>
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./css/style.css">
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
                                <?php if(isset($success_message)): ?>
                                <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 50px;">
                                  <div class="toast" id="successToast" style="position: absolute; top: 0; right: 0; min-width: 250px; z-index: 1050;" data-delay="3000" data-autohide="true">
                                    <div class="toast-header bg-success text-white">
                                      <strong class="mr-auto">Success</strong>
                                      <small>Now</small>
                                      <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                      </button>
                                    </div>
                                    <div class="toast-body">
                                      <?php echo $success_message; ?>
                                    </div>
                                  </div>
                                </div>
                                <script>
                                  window.addEventListener('DOMContentLoaded', function(){
                                    var toastEl = document.getElementById('successToast');
                                    if(toastEl && window.$) {
                                      $(toastEl).toast('show');
                                    } else if (toastEl && typeof bootstrap !== "undefined") {
                                      var toast = new bootstrap.Toast(toastEl);
                                      toast.show();
                                    }
                                  });
                                </script>
                                <?php endif; ?>
                                <form class="forms-sample" method="post">
                                    <div class="form-group">
                                        <label>ID</label>
                                        <input type="text" value="<?php echo htmlentities($result->ID); ?>" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="name" value="<?php echo htmlentities($result->StaffName); ?>" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>User Name</label>
                                        <input type="text" name="username" value="<?php echo htmlentities($result->UserName); ?>" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" value="<?php echo htmlentities($result->Email); ?>" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Change Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep unchanged">
                                    </div>
                                    <div class="form-group">
                                        <label>StaffRegdate</label>
                                        <input type="text" value="<?php echo htmlentities($result->StaffRegdate); ?>" class="form-control" readonly>
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
</body>
</html>
<?php } ?>