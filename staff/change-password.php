<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid']) == 0) {
  header('location:logout.php');
} else {
  $toast_msg = null;
  $toast_type = 'info';

  if (isset($_POST['change_password'])) {
    $staffid = $_SESSION['sturecmsstaffid'];
    $current = isset($_POST['currentpassword']) ? trim($_POST['currentpassword']) : '';
    $new = isset($_POST['newpassword']) ? trim($_POST['newpassword']) : '';
    $confirm = isset($_POST['confirmpassword']) ? trim($_POST['confirmpassword']) : '';

    if ($new === '' || $confirm === '') {
      $toast_msg = 'New password cannot be empty';
      $toast_type = 'warning';
    } elseif ($new !== $confirm) {
      $toast_msg = 'New Password and Confirm Password do not match';
      $toast_type = 'warning';
    } else {
      $sql = "SELECT Password FROM tblstaff WHERE ID=:staffid";
      $query = $dbh->prepare($sql);
      $query->bindParam(':staffid', $staffid, PDO::PARAM_STR);
      $query->execute();
      $result = $query->fetch(PDO::FETCH_OBJ);

      if ($result && password_verify($current, $result->Password)) {
        $new_hashed = password_hash($new, PASSWORD_DEFAULT);
        $con = "UPDATE tblstaff SET Password=:newpassword WHERE ID=:staffid";
        $chngpwd1 = $dbh->prepare($con);
        $chngpwd1->bindParam(':staffid', $staffid, PDO::PARAM_STR);
        $chngpwd1->bindParam(':newpassword', $new_hashed, PDO::PARAM_STR);
        $chngpwd1->execute();
        $toast_msg = 'Your password was successfully changed.';
        $toast_type = 'success';
      } else {
        $toast_msg = 'Your current password is incorrect.';
        $toast_type = 'warning';
      }
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Staff Profiling System || Change Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css" />
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
              <h3 class="page-title">Change Password</h3>
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
                        <label for="currentpassword">Current Password</label>
                        <input type="password" name="currentpassword" id="currentpassword" class="form-control"
                          required="true">
                        <i class="icon-eye" id="toggleCurrentPassword"
                          style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                      </div>
                      <div class="form-group" style="position: relative;">
                        <label for="newpassword">New Password</label>
                        <input type="password" id="newpassword" name="newpassword" class="form-control" required="true">
                        <i class="icon-eye" id="toggleNewPassword"
                          style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                      </div>
                      <div class="form-group" style="position: relative;">
                        <label for="confirmpassword">Confirm Password</label>
                        <input type="password" name="confirmpassword" id="confirmpassword" class="form-control"
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
          <?php include_once('includes/footer.php'); ?>
        </div>
      </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="vendors/select2/select2.min.js"></script>
    <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/script.js"></script>
    <script src="js/typeahead.js"></script>
    <script src="js/select2.js"></script>
    <script src="js/toast.js"></script>
    <?php if (isset($toast_msg) && $toast_msg): ?>
      <script>
        document.addEventListener('DOMContentLoaded', function () { if (window.showToast) showToast(<?php echo json_encode($toast_msg); ?>, <?php echo json_encode($toast_type); ?>); });
      </script>
    <?php endif; ?>
  </body>

  </html>
<?php } ?>