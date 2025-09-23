<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid'] == 0)) { // Ensure staff session is checked
  header('location:logout.php');
} else {
  if (isset($_POST['submit'])) {
    $staffid = $_SESSION['sturecmsstaffid'];
    $SName = $_POST['staffname'];
    $email = $_POST['email'];
    $sql = "UPDATE tblstaff SET StaffName=:staffname, Email=:email WHERE ID=:staffid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':staffname', $SName, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':staffid', $staffid, PDO::PARAM_STR);
    $query->execute();

    echo '<script>alert("Your profile has been updated")</script>';
    echo "<script>window.location.href ='profile.php'</script>";
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <title>Staff Management System | Profile</title>
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
            <h3 class="page-title"> Staff Profile </h3>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Staff Profile</li>
              </ol>
            </nav>
          </div>
          <div class="row">

            <div class="col-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title" style="text-align: center;">Staff Profile</h4>

                  <form class="forms-sample" method="post">
                    <?php
                    $staffid = $_SESSION['sturecmsstaffid'];
                    $sql = "SELECT * FROM tblstaff WHERE ID=:staffid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':staffid', $staffid, PDO::PARAM_STR);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                    if ($query->rowCount() > 0) {
                      foreach ($results as $row) { ?>
                        <div class="form-group">
                          <label for="exampleInputName1">Staff Name</label>
                          <input type="text" name="staffname" value="<?php echo htmlentities($row->StaffName); ?>" class="form-control" required='true'>
                        </div>
                        <div class="form-group">
                          <label for="exampleInputEmail3">User Name</label>
                          <input type="text" name="username" value="<?php echo htmlentities($row->UserName); ?>" class="form-control" readonly="">
                        </div>
                        <div class="form-group">
                          <label for="exampleInputCity1">Email</label>
                          <input type="email" name="email" value="<?php echo htmlentities($row->Email); ?>" class="form-control" required='true'>
                        </div>
                        <div class="form-group">
                          <label for="exampleInputCity1">Staff Registration Date</label>
                          <input type="text" name="" value="<?php echo htmlentities($row->StaffRegdate); ?>" readonly="" class="form-control">
                        </div>
                    <?php }
                    } ?>
                    <button type="submit" class="btn btn-primary mr-2" name="submit">Update</button>

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
  <!-- Custom js for this page -->
  <script src="js/typeahead.js"></script>
  <script src="js/select2.js"></script>
  <!-- End custom js for this page -->
</body>

</html>
<?php } ?>