<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsstuid'] == 0)) {
  header('location:logout.php');
} else {
  if (isset($_POST['submit'])) {
    $sid = $_SESSION['sturecmsstuid'];
    $currentpassword = $_POST['currentpassword'];
    $newpassword = $_POST['newpassword'];

    $sql = "SELECT Password FROM tblstudent WHERE StuID=:sid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':sid', $sid, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result && (password_verify($currentpassword, $result->Password) || md5($currentpassword) === $result->Password)) {
      $new_hashed_password = password_hash($newpassword, PASSWORD_DEFAULT);
      $con = "update tblstudent set Password=:newpassword where StuID=:sid";
      $chngpwd1 = $dbh->prepare($con);
      $chngpwd1->bindParam(':sid', $sid, PDO::PARAM_STR);
      $chngpwd1->bindParam(':newpassword', $new_hashed_password, PDO::PARAM_STR);
      $chngpwd1->execute();

      $_SESSION['toast_message'] = ['type' => 'success', 'message' => 'Your password was successfully changed.'];
    } else {
      $_SESSION['toast_message'] = ['type' => 'warning', 'message' => 'Your current password is wrong.'];
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Student Management System|| Student Change Password</title>
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
      .toast-box { position: fixed; top: 20px; right: 20px; z-index: 1050; width: 300px; background: #fff; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 4px; display: none; }
      .toast-box .toast-header { padding: 10px 15px; border-bottom: 1px solid #eee; font-weight: 600; }
      .toast-box .toast-body { padding: 15px; }
      .toast-box.toast-success .toast-header { background-color: #d4edda; color: #155724; }
      .toast-box.toast-warning .toast-header { background-color: #fff3cd; color: #856404; }
      .toast-box.toast-danger .toast-header { background-color: #f8d7da; color: #721c24; }
      .toast-show { display: block; animation: toast-in 0.3s; }
      @keyframes toast-in { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
    <script type="text/javascript">
      function showToast(message, type) {
        var toast = document.getElementById('pageToast');
        if (!toast) return;
        toast.className = 'toast-box toast-show toast-' + type;
        toast.querySelector('.toast-body').textContent = message;
        setTimeout(function() { toast.className = toast.className.replace('toast-show', ''); }, 4000);
      }

      function checkpass() {
        if (document.changepassword.newpassword.value != document.changepassword.confirmpassword.value) {
          showToast('New Password and Confirm Password field does not match', 'warning');
          document.changepassword.confirmpassword.focus();
          return false;
        }
        return true;
      }

    </script>
  </head>

  <body>
    <div class="container-scroller">
      <div id="pageToast" class="toast-box"><div class="toast-header">Notification</div><div class="toast-body"></div></div>
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
                        <i class="icon-eye" id="toggleCurrentPassword" style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                      </div>
                      <div class="form-group" style="position: relative;">
                        <label for="exampleInputEmail3">New Password</label>
                        <input type="password" name="newpassword" id="newpassword" class="form-control" required="true">
                        <i class="icon-eye" id="toggleNewPassword" style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                      </div>
                      <div class="form-group" style="position: relative;">
                        <label for="exampleInputPassword4">Confirm Password</label>
                        <input type="password" name="confirmpassword" id="confirmpassword" value="" class="form-control"
                          required="true">
                        <i class="icon-eye" id="toggleConfirmPassword"
                          style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                      </div>

                      <button type="submit" class="btn btn-primary mr-2" name="submit">Change</button>

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
    <script src="js/script.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="js/typeahead.js"></script>
    <script src="js/select2.js"></script>
    <?php
    if (isset($_SESSION['toast_message'])) {
      $toast = $_SESSION['toast_message'];
      echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('" . addslashes($toast['message']) . "', '" . addslashes($toast['type']) . "'); });</script>";
      unset($_SESSION['toast_message']);
    }
    ?>
    <!-- End custom js for this page -->
  </body>

  </html><?php } ?>