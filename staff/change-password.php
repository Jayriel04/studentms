<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid'] == 0)) { // Ensure staff session is checked
  header('location:logout.php');
} else {
  function sendOtp($email, $code) {
    $subject = "Your Password Change OTP";
    $message = "Your OTP for password change is: $code";
    @mail($email, $subject, $message);
  }

  if (isset($_POST['request_otp'])) {
    $staffid = $_SESSION['sturecmsstaffid'];
    $cpassword = md5($_POST['currentpassword']);
    $newpassword_plain = $_POST['newpassword'];
    $newpassword_hashed = md5($newpassword_plain);

    $sql = "SELECT ID, Email FROM tblstaff WHERE ID=:staffid and Password=:cpassword";
    $query = $dbh->prepare($sql);
    $query->bindParam(':staffid', $staffid, PDO::PARAM_STR);
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
      $_SESSION['change_user_id'] = $staffid;
      $_SESSION['change_user_email'] = $email;
  echo "<script>if(window.showToast) showToast('An OTP has been sent to your registered email.','info'); else alert('An OTP has been sent to your registered email.');</script>";
    } else {
  echo "<script>if(window.showToast) showToast('Your current password is incorrect.','warning'); else alert('Your current password is incorrect.');</script>";
    }
  }

  if (isset($_POST['verify_otp'])) {
    $input_code = trim($_POST['input_code']);
    if (!empty($_SESSION['change_otp']) && $_SESSION['change_otp_expires'] >= time() && $input_code == $_SESSION['change_otp']) {
      $userid = $_SESSION['change_user_id'];
      $newpassword = $_SESSION['change_pending_password'];
      $con = "UPDATE tblstaff SET Password=:newpassword WHERE ID=:staffid";
      $chngpwd1 = $dbh->prepare($con);
      $chngpwd1->bindParam(':staffid', $userid, PDO::PARAM_STR);
      $chngpwd1->bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
      $chngpwd1->execute();
  echo "<script>if(window.showToast) showToast('Your password has been successfully changed.','success'); else alert('Your password has been successfully changed.');</script>";
      unset($_SESSION['change_otp'], $_SESSION['change_otp_expires'], $_SESSION['change_pending_password'], $_SESSION['change_user_id'], $_SESSION['change_user_email']);
    } else {
  echo "<script>if(window.showToast) showToast('Invalid or expired OTP','danger'); else alert('Invalid or expired OTP');</script>";
    }
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Staff Management System | Change Password</title>
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
  if(window.showToast) showToast('New Password and Confirm Password fields do not match.','warning'); else alert('New Password and Confirm Password fields do not match.');
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
                  <?php if (!isset($_SESSION['change_otp'])): ?>
                  <form class="forms-sample" name="changepassword" method="post" onsubmit="return checkpass();">
                    <div class="form-group">
                      <label for="currentpassword">Current Password</label>
                      <input type="password" name="currentpassword" id="currentpassword" class="form-control" required="true">
                    </div>
                    <div class="form-group">
                      <label for="newpassword">New Password</label>
                      <input type="password" name="newpassword" class="form-control" required="true">
                    </div>
                    <div class="form-group">
                      <label for="confirmpassword">Confirm Password</label>
                      <input type="password" name="confirmpassword" id="confirmpassword" class="form-control" required="true">
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

</html>
<?php } ?>