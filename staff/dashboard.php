<?php
session_start();
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid'] == 0)) {
  header('location:logout.php');
} else {
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Profiling System || Staff Dashboard</title>
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
  </head>

  <body>
    <div class="container-scroller">
      <?php include_once('includes/header.php'); ?>
      <div class="container-fluid page-body-wrapper">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-md-12 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="d-sm-flex align-items-baseline report-summary-header">
                          <h5 class="font-weight-semibold">Staff Dashboard</h5> <span class="ml-auto">Welcome,
                            Staff</span>
                        </div>
                      </div>
                    </div>
                    <div class="row report-inner-cards-wrapper">
                      <div class="col-md-6 col-xl report-inner-card">
                        <div class="inner-card-text">
                          <?php
                          $sql2 = "SELECT * from tblstudent";
                          $query2 = $dbh->prepare($sql2);
                          $query2->execute();
                          $totstu = $query2->rowCount();
                          ?>
                          <span class="report-title">Total Students</span>
                          <h4><?php echo htmlentities($totstu); ?></h4>
                          <a href="manage-students.php"><span class="report-count"> View Students</span></a>
                        </div>
                        <div class="inner-card-icon bg-danger">
                          <i class="icon-user"></i>
                        </div>
                      </div>
                      <div class="col-md-6 col-xl report-inner-card">
                        <div class="inner-card-text">
                          <?php
                          $sql4 = "SELECT * from tblpublicnotice";
                          $query4 = $dbh->prepare($sql4);
                          $query4->execute();
                          $totpublicnotice = $query4->rowCount();
                          ?>
                          <span class="report-title">Total Public Notice</span>
                          <h4><?php echo htmlentities($totpublicnotice); ?></h4>
                          <a href="manage-public-notice.php"><span class="report-count"> View Public Notices</span></a>
                        </div>
                        <div class="inner-card-icon bg-primary">
                          <i class="icon-doc"></i>
                        </div>
                      </div>
                      <div class="col-md-6 col-xl report-inner-card">
                        <div class="inner-card-text">
                          <?php
                          $sqlPending = "SELECT COUNT(*) FROM student_achievements WHERE status = 'pending'";
                          $qPending = $dbh->prepare($sqlPending);
                          $qPending->execute();
                          $pendingCount = (int)$qPending->fetchColumn();
                          ?>
                          <span class="report-title">Pending Validations</span>
                          <h4><?php echo htmlentities($pendingCount); ?></h4>
                          <a href="validate-achievements.php"><span class="report-count"> Review Pending</span></a>
                        </div>
                        <div class="inner-card-icon bg-warning">
                          <i class="icon-check"></i>
                        </div>
                      </div>
                    </div>
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