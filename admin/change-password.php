<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
error_reporting(0);
if (strlen($_SESSION['sturecmsaid'] == 0)) {
  header('location:logout.php');
} else {
  function sendOtp($email, $code) {
    $subject = "Your Password Change OTP";
    $message = "Your OTP for password change is: $code";
    @mail($email, $subject, $message);
  }

  if (isset($_POST['request_otp'])) {
    $adminid = $_SESSION['sturecmsaid'];
    $cpassword = md5($_POST['currentpassword']);
    $newpassword_plain = $_POST['newpassword'];
    $newpassword_hashed = md5($newpassword_plain);

    $sql = "SELECT ID, Email FROM tbladmin WHERE ID=:adminid and Password=:cpassword";
    $query = $dbh->prepare($sql);
    $query->bindParam(':adminid', $adminid, PDO::PARAM_STR);
    $query->bindParam(':cpassword', $cpassword, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() > 0) {
      $row = $query->fetch(PDO::FETCH_ASSOC);
      $email = $row['Email'];
      $code = rand(100000, 999999);
      sendOtp($email, $code);
      $_SESSION['change_otp'] = $code;
      $_SESSION['change_otp_expires'] = time() + 600;
      $_SESSION['change_pending_password'] = $newpassword_hashed;
      $_SESSION['change_user_id'] = $adminid;
      $_SESSION['change_user_email'] = $email;
      echo '<script>alert("An OTP has been sent to your registered email.")</script>';
    } else {
      echo '<script>alert("Your current password is wrong")</script>';
    }
  }

  if (isset($_POST['verify_otp'])) {
    $input_code = trim($_POST['input_code']);
    if (!empty($_SESSION['change_otp']) && $_SESSION['change_otp_expires'] >= time() && $input_code == $_SESSION['change_otp']) {
      $userid = $_SESSION['change_user_id'];
      $newpassword = $_SESSION['change_pending_password'];
      $con = "update tbladmin set Password=:newpassword where ID=:adminid";
      $chngpwd1 = $dbh->prepare($con);
      $chngpwd1->bindParam(':adminid', $userid, PDO::PARAM_STR);
      $chngpwd1->bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
      $chngpwd1->execute();
      echo '<script>alert("Your password successully changed")</script>';
      unset($_SESSION['change_otp'], $_SESSION['change_otp_expires'], $_SESSION['change_pending_password'], $_SESSION['change_user_id'], $_SESSION['change_user_email']);
    } else {
      echo '<script>alert("Invalid or expired OTP")</script>';
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>

    <title>Student Management System|| Change Password</title>
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
    <script type="text/javascript">
      function checkpass() {
        if (document.changepassword.newpassword.value != document.changepassword.confirmpassword.value) {
          alert('New Password and Confirm Password field does not match');
          document.changepassword.confirmpassword.focus();
          return false;
        }
        return true;
      }

    </script>
  </head>

  <body>
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

                    <?php if (!isset($_SESSION['change_otp'])): ?>
                    <form class="forms-sample" name="changepassword" method="post" onsubmit="return checkpass();">

                      <div class="form-group">
                        <label for="exampleInputName1">Current Password</label>
                        <input type="password" name="currentpassword" id="currentpassword" class="form-control"
                          required="true">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail3">New Password</label>
                        <input type="password" name="newpassword" class="form-control" required="true">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputPassword4">Confirm Password</label>
                        <input type="password" name="confirmpassword" id="confirmpassword" value="" class="form-control"
                          required="true">
                      </div>

                      <button type="submit" class="btn btn-primary mr-2" name="request_otp">Request OTP</button>

                    </form>
                    <?php else: ?>
                    <form class="forms-sample" name="verifyotp" method="post">
                      <div class="form-group">
                        <label for="input_code">Enter OTP</label>
                        <input type="text" name="input_code" class="form-control" required="true">
                      </div>
                      <button type="submit" class="btn btn-primary mr-2" name="verify_otp">Verify & Change</button>
                    </form>
                    <?php endif; ?>
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
    <!-- Custom js for this page -->
    <script src="js/typeahead.js"></script>
    <script src="js/select2.js"></script>
    <!-- End custom js for this page -->
  </body>

  </html><?php } ?>