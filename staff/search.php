<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid'] == 0)) { // Ensure staff session is checked
  header('location:logout.php');
} else {
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Staff Management System | Search Students</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="./vendors/daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="./vendors/chartist/chartist.min.css">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <!-- endinject -->
  <!-- Layout styles -->
  <link rel="stylesheet" href="./css/style.css">
  <!-- End layout styles -->
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
            <h3 class="page-title">Search Students</h3>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Search Students</li>
              </ol>
            </nav>
          </div>
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <form method="post">
                    <div class="form-group">
                      <strong>Search Student:</strong>
                      <input id="searchdata" type="text" name="searchdata" required="true" class="form-control"
                        placeholder="Search by Student ID or Student Name">
                    </div>
                    <button type="submit" class="btn btn-primary" name="search" id="submit">Search</button>
                  </form>
                  <div class="d-sm-flex align-items-center mb-4">
                    <?php
                    if (isset($_POST['search'])) {
                      $sdata = $_POST['searchdata'];
                    ?>
                      <hr />
                      <h4 align="center">Result against "<?php echo $sdata; ?>" keyword</h4>
                  </div>
                  <div class="table-responsive border rounded p-1">
                    <table class="table">
                      <thead>
                        <tr>
                          <th class="font-weight-bold">S.No</th>
                          <th class="font-weight-bold">Student ID</th>
                          <th class="font-weight-bold">Student Name</th>
                          <th class="font-weight-bold">Student Email</th>
                          <th class="font-weight-bold">Admission Date</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $sql = "SELECT StuID, ID as sid, StudentName, StudentEmail, DateofAdmission FROM tblstudent WHERE StuID LIKE :sdata OR StudentName LIKE :sdata";
                        $query = $dbh->prepare($sql);
                        $query->bindValue(':sdata', '%' . $sdata . '%', PDO::PARAM_STR);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);

                        $cnt = 1;
                        if ($query->rowCount() > 0) {
                          foreach ($results as $row) { ?>
                            <tr>
                              <td><?php echo htmlentities($cnt); ?></td>
                              <td><?php echo htmlentities($row->StuID); ?></td>
                              <td><?php echo htmlentities($row->StudentName); ?></td>
                              <td><?php echo htmlentities($row->StudentEmail); ?></td>
                              <td><?php echo htmlentities($row->DateofAdmission); ?></td>
                            </tr>
                        <?php $cnt++;
                          }
                        } else { ?>
                          <tr>
                            <td colspan="5" style="text-align: center; color: red;">No record found against this search</td>
                          </tr>
                        <?php }
                    } ?>
                      </tbody>
                    </table>
                  </div>
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
  <script src="./vendors/chart.js/Chart.min.js"></script>
  <script src="./vendors/moment/moment.min.js"></script>
  <script src="./vendors/daterangepicker/daterangepicker.js"></script>
  <script src="./vendors/chartist/chartist.min.js"></script>
  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="js/off-canvas.js"></script>
  <script src="js/misc.js"></script>
  <!-- endinject -->
  <!-- Custom js for this page -->
  <script src="./js/dashboard.js"></script>
  <!-- End custom js for this page -->
</body>

</html>
<?php } ?>