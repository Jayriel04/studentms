<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
  header('location:logout.php');
} else {
  if (isset($_POST['submit'])) {
    $staffname = $_POST['staffname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $regdate = date('Y-m-d H:i:s');

    $ret = "SELECT UserName FROM tblstaff WHERE UserName = :username";
    $query = $dbh->prepare($ret);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    if ($query->rowCount() == 0) {
      $sql = "INSERT INTO tblstaff (StaffName, UserName, Email, Password, StaffRegdate) VALUES (:staffname, :username, :email, :password, :regdate)";
      $query = $dbh->prepare($sql);
      $query->bindParam(':staffname', $staffname, PDO::PARAM_STR);
      $query->bindParam(':username', $username, PDO::PARAM_STR);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->bindParam(':password', $password, PDO::PARAM_STR);
      $query->bindParam(':regdate', $regdate, PDO::PARAM_STR);
      $query->execute();
      $LastInsertId = $dbh->lastInsertId();
      if ($LastInsertId > 0) {
        $success_message = "Staff has been added.";
        // do not echo alert here; success toast is shown by HTML below
      } else {
        echo "<script>if(window.showToast) showToast('Something Went Wrong. Please try again','danger'); else alert('Something Went Wrong. Please try again');</script>";
      }
    } else {
      echo "<script>if(window.showToast) showToast('Username already exists. Please try again','warning'); else alert('Username already exists. Please try again');</script>";
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Student Profiling System || Add Staff</title>
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
              <h3 class="page-title">Add Staff</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Add Staff</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h3 class="card-title" style="text-align: center;">Add Staff</h3>
                    <hr />
                    <?php if (isset($success_message)): ?>
                      <!-- Toast for success -->
                      <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 50px;">
                        <div class="toast" id="successToast"
                          style="position: absolute; top: 0; right: 0; min-width: 250px; z-index: 1050;" data-delay="3000"
                          data-autohide="true">
                          <div class="toast-header bg-success text-white">
                            <strong class="mr-auto">Success</strong>
                            <small>Now</small>
                            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast"
                              aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="toast-body">
                            <?php echo $success_message; ?>
                          </div>
                        </div>
                      </div>
                      <script>
                        window.addEventListener('DOMContentLoaded', function () {
                          var toastEl = document.getElementById('successToast');
                          if (toastEl && window.$) {
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
                        <label for="staffname">Staff Name</label>
                        <input type="text" name="staffname" class="form-control" required='true'>
                      </div>
                      <div class="form-group">
                        <label for="username">User Name</label>
                        <input type="text" name="username" class="form-control" required='true'>
                      </div>
                      <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" required='true'>
                      </div>
                      <div class="form-group" style="position: relative;">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required='true'>
                        <i class="icon-eye" id="togglePassword"
                          style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                      </div>
                      <button type="submit" class="btn btn-primary mr-2" name="submit">Add</button>
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
  </body>

  </html><?php } ?>