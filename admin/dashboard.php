<?php
session_start();
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
  header('location:logout.php');
} else {

  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>

    <title>Student Profiling System || Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="../images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../images/android-chrome-192x192.png">
    <link rel="manifest" href="../images/site.webmanifest">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="vendors/chartist/chartist.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/style(v2).css">
    <link rel="stylesheet" href="./css/responsive.css">
  </head>

  <body>
    <div class="container-scroller">
      <?php include_once('includes/header.php'); ?>
      <div class="container-fluid page-body-wrapper">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="dashboard-container">
              <!-- Stats Grid -->
              <div class="stats-grid">
                <!-- Total Students -->
                <a href="manage-students.php" class="stat-card">
                  <div class="stat-header">
                    <div class="stat-icon">👥</div>
                    <div class="menu-dots"></div>
                  </div>
                  <div class="stat-label">Total Students</div>
                  <div class="stat-value-row">
                    <?php
                    $sql_students = "SELECT COUNT(ID) FROM tblstudent";
                    $q_students = $dbh->query($sql_students);
                    $totstu = $q_students->fetchColumn();
                    ?>
                    <div class="stat-value"><?php echo htmlentities($totstu); ?></div>
                  </div>
                </a>

                <!-- Total Notices -->
                <a href="manage-notice.php" class="stat-card">
                  <div class="stat-header">
                    <div class="stat-icon">📢</div>
                    <div class="menu-dots"></div>
                  </div>
                  <div class="stat-label">Notices</div>
                  <div class="stat-value-row">
                    <?php
                    $sql_notices = "SELECT COUNT(ID) FROM tblnotice";
                    $q_notices = $dbh->query($sql_notices);
                    $totpublicnotice = $q_notices->fetchColumn();
                    ?>
                    <div class="stat-value"><?php echo htmlentities($totpublicnotice); ?></div>
                  </div>
                </a>

                <!-- Total Staff -->
                <a href="manage-staff.php" class="stat-card">
                  <div class="stat-header">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="menu-dots"></div>
                  </div>
                  <div class="stat-label">Total Staff</div>
                  <div class="stat-value-row">
                    <?php
                    $sql_staff = "SELECT COUNT(ID) FROM tblstaff";
                    $q_staff = $dbh->query($sql_staff);
                    $totstaff = $q_staff->fetchColumn();
                    ?>
                    <div class="stat-value"><?php echo htmlentities($totstaff); ?></div>
                  </div>
                </a>

                <!-- Pending Validations -->
                <a href="validate-achievements.php" class="stat-card">
                  <div class="stat-header">
                    <div class="stat-icon">⏳</div>
                    <div class="menu-dots"></div>
                  </div>
                  <div class="stat-label">Pending Validations</div>
                  <div class="stat-value-row">
                    <?php
                    $sqlPending = "SELECT COUNT(*) FROM student_achievements WHERE status = 'pending'";
                    $qPending = $dbh->prepare($sqlPending);
                    $qPending->execute();
                    $pendingCount = (int) $qPending->fetchColumn();
                    ?>
                    <div class="stat-value"><?php echo htmlentities($pendingCount); ?></div>
                  </div>
                </a>
              </div>

              <!-- Content Grid -->
              <div class="content-grid">
                <!-- Analytics Card -->
                <div class="analytics-card">
                  <div class="card-header">
                    <h2 class="card-title">Student Population</h2>
                  </div>
                  <div class="chart-container">
                    <?php
                    // Count students per year level
                    $sqlYearLevels = "SELECT YearLevel, COUNT(ID) as student_count FROM tblstudent WHERE YearLevel IN ('1', '2', '3', '4') GROUP BY YearLevel ORDER BY YearLevel ASC";
                    $queryYearLevels = $dbh->prepare($sqlYearLevels);
                    $queryYearLevels->execute();
                    $yearLevelResults = $queryYearLevels->fetchAll(PDO::FETCH_OBJ);

                    $yearCounts = ['1' => 0, '2' => 0, '3' => 0, '4' => 0];
                    foreach ($yearLevelResults as $row) {
                      if (isset($yearCounts[$row->YearLevel])) {
                        $yearCounts[$row->YearLevel] = (int) $row->student_count;
                      }
                    }

                    $yearLevelData = [
                      ['name' => '1st Year', 'student_count' => $yearCounts['1']],
                      ['name' => '2nd Year', 'student_count' => $yearCounts['2']],
                      ['name' => '3rd Year', 'student_count' => $yearCounts['3']],
                      ['name' => '4th Year', 'student_count' => $yearCounts['4']],
                    ];
                    ?>
                    <canvas id="yearLevelChart" data-year-levels='<?php echo json_encode($yearLevelData); ?>'></canvas>
                  </div>
                </div>

                <!-- Recent Notices Section -->
                <div class="cards-section">
                  <div class="cards-header">
                    <h2 class="card-title">Recent Notices</h2>
                    <a href="manage-notice.php" class="see-all-link">See all</a>
                  </div>
                  <?php
                  // Fetch recent notices
                  $sql = "SELECT NoticeTitle, CreationDate, NoticeMsg FROM tblnotice ORDER BY CreationDate DESC LIMIT 4";
                  $query = $dbh->prepare($sql);
                  $query->execute();
                  $notices = $query->fetchAll(PDO::FETCH_OBJ);

                  if (empty($notices)) {
                    echo '<p>No recent notices found.</p>';
                  } else {
                    echo '<ul class="list-group list-group-flush">';
                    foreach ($notices as $notice) {
                      $title = htmlentities($notice->NoticeTitle);
                      $date = date('M j, Y', strtotime($notice->CreationDate));
                      $msg = nl2br(htmlentities($notice->NoticeMsg));
                      $full_date = htmlentities($notice->CreationDate);
                      ?>
                      <li class="list-group-item notice-list-item"
                        onclick="showNoticeDetail('<?php echo $title; ?>', '<?php echo $full_date; ?>', '<?php echo str_replace(array("\r", "\n", "'"), array(" ", "\\n", "\\'"), $msg); ?>')">
                        <div class="d-flex w-100 justify-content-between">
                          <h6 class="mb-1"><?php echo $title; ?></h6>
                          <small><?php echo $date; ?></small>
                        </div>
                      </li>
                      <?php
                    }
                    echo '</ul>';
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="vendors/chart.js/Chart.min.js"></script>
    <script src="js/toast.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/script.js"></script>
    <script src="js/chart.js"></script>
  </body>

  </html><?php } ?>