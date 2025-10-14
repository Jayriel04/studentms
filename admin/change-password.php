<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
error_reporting(0);
if (strlen($_SESSION['sturecmsaid'] == 0)) {
  header('location:logout.php');
} else {
  // Simple password change: verify current password and update directly
  if (isset($_POST['change_password'])) {
    $adminid = $_SESSION['sturecmsaid'];
    $current = isset($_POST['currentpassword']) ? trim($_POST['currentpassword']) : '';
    $new = isset($_POST['newpassword']) ? trim($_POST['newpassword']) : '';
    $confirm = isset($_POST['confirmpassword']) ? trim($_POST['confirmpassword']) : '';

    if ($new === '' || $confirm === '') {
      $toast_msg = 'New password cannot be empty';
      $toast_type = 'error';
    } elseif ($new !== $confirm) {
      $toast_msg = 'New Password and Confirm Password do not match';
      $toast_type = 'error';
    } else {
      // Fetch current password hash from DB
      $sql = "SELECT Password FROM tbladmin WHERE ID=:adminid";
      $query = $dbh->prepare($sql);
      $query->bindParam(':adminid', $adminid, PDO::PARAM_STR);
      $query->execute();
      $result = $query->fetch(PDO::FETCH_OBJ);

      if ($result && password_verify($current, $result->Password)) {
        // Current password is correct, proceed to update
        $new_hashed = password_hash($new, PASSWORD_DEFAULT);
        if ($new_hashed) {
          $con = "UPDATE tbladmin SET Password=:newpassword WHERE ID=:adminid";
          $chngpwd1 = $dbh->prepare($con);
          $chngpwd1->bindParam(':adminid', $adminid, PDO::PARAM_STR);
          $chngpwd1->bindParam(':newpassword', $new_hashed, PDO::PARAM_STR);
          $chngpwd1->execute();
          $toast_msg = 'Your password successfully changed';
          $toast_type = 'success';
        } else {
          $toast_msg = 'Error creating new password hash.';
          $toast_type = 'error';
        }
      } else {
        $toast_msg = 'Your current password is wrong';
        $toast_type = 'error';
      }
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>

    <title>Student Profiling System|| Change Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="css/style.css" />
    <style>
      /* Toast container positioned top-right */
      #appToast { position: fixed; top: 1rem; right: 1rem; z-index: 2000; }
    </style>
  </head>

  <body>
    <div id="appToast"></div>
    <div class="container-scroller">
      <!-- partial:partials/_navbar.html -->
      <?php include_once('includes/header.php'); ?>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title"> Change Password </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                </ol>
              </nav>
            </div>
            <div class="row">

              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Change Password</h4>

                    <form class="forms-sample" name="changepassword" method="post" onsubmit="return checkpass();">

                      <div class="form-group" style="position: relative;">
                        <label for="exampleInputName1">Current Password</label>
                        <input type="password" name="currentpassword" id="currentpassword" class="form-control"
                          required="true">
                        <i class="icon-eye" id="toggleCurrentPassword"
                          style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                      </div>
                      <div class="form-group" style="position: relative;">
                        <label for="exampleInputEmail3">New Password</label>
                        <input type="password" name="newpassword" id="newpassword" class="form-control" required="true">
                        <i class="icon-eye" id="toggleNewPassword"
                          style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                      </div>
                      <div class="form-group" style="position: relative;">
                        <label for="exampleInputPassword4">Confirm Password</label>
                        <input type="password" name="confirmpassword" id="confirmpassword" value="" class="form-control"
                          required="true">
                        <i class="icon-eye" id="toggleConfirmPassword"
                          style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                      </div>

                      <button type="submit" class="btn btn-primary mr-2" name="change_password">Change Password</button>
                      <a href="dashboard.php" class="btn btn-light">Back</a>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- content-wrapper ends -->
          <!-- partial:partials/_footer.html -->
          <?php include_once('includes/footer.php'); ?>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="vendors/select2/select2.min.js"></script>
    <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <!-- endinject -->
    <script src="js/script.js"></script>

    <?php if (isset($toast_msg) && $toast_msg): ?>
    <script>document.addEventListener('DOMContentLoaded', function(){ showToast(<?php echo json_encode($toast_msg); ?>, <?php echo json_encode(isset($toast_type) ? $toast_type : 'info'); ?>); });</script>
    <?php endif; ?>
  </body>

  </html><?php } ?>