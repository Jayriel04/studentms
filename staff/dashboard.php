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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/style(v2).css">
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
                    <div class="row">
                      <!-- Total Students -->
                      <div class="col-md-6 col-xl-4 grid-margin stretch-card">
                        <div class="card report-card bg-c-danger">
                          <?php
                          $sql_students = "SELECT COUNT(ID) FROM tblstudent";
                          $q_students = $dbh->query($sql_students);
                          $totstu = $q_students->fetchColumn();
                          ?>
                          <p class="card-title">Total Students</p>
                          <h2 class="card-value"><?php echo htmlentities($totstu); ?></h2>
                          <a class="card-link" href="manage-students.php">View Students <i class="icon-arrow-right"></i></a>
                        </div>
                      </div>

                      <!-- Total Public Notices -->
                      <div class="col-md-6 col-xl-4 grid-margin stretch-card">
                        <div class="card report-card bg-c-primary">
                          <?php
                          $sql_notices = "SELECT COUNT(ID) FROM tblpublicnotice";
                          $q_notices = $dbh->query($sql_notices);
                          $totpublicnotice = $q_notices->fetchColumn();
                          ?>
                          <p class="card-title">Public Notices</p>
                          <h2 class="card-value"><?php echo htmlentities($totpublicnotice); ?></h2>
                          <a class="card-link" href="manage-public-notice.php">View Notices <i class="icon-arrow-right"></i></a>
                        </div>
                      </div>

                      <!-- Pending Validations -->
                      <div class="col-md-6 col-xl-4 grid-margin stretch-card">
                        <div class="card report-card bg-c-warning">
                          <?php
                          $sqlPending = "SELECT COUNT(*) FROM student_achievements WHERE status = 'pending'";
                          $qPending = $dbh->prepare($sqlPending);
                          $qPending->execute();
                          $pendingCount = (int) $qPending->fetchColumn();
                          ?>
                          <p class="card-title">Pending Validations</p>
                          <h2 class="card-value"><?php echo htmlentities($pendingCount); ?></h2>
                          <a class="card-link" href="validate-achievements.php">Review Pending <i class="icon-arrow-right"></i></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Calendar Notice -->
            <div class="row">
              <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="calendar-container">
                      <div class="calendar-header">
                        <div class="calendar-title">Notice Calendar</div>
                        <?php
                        $month = isset($_GET['month']) ? (int) $_GET['month'] : date('n');
                        $year = isset($_GET['year']) ? (int) $_GET['year'] : date('Y');
                        $prev_month = $month == 1 ? 12 : $month - 1;
                        $prev_year = $month == 1 ? $year - 1 : $year;
                        $next_month = $month == 12 ? 1 : $month + 1;
                        $next_year = $month == 12 ? $year + 1 : $year;
                        ?>
                        <div>
                          <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>"
                            class="btn btn-outline-primary btn-sm">&lt; Prev</a>
                          <span
                            style="font-weight:600; margin:0 10px;"><?php echo date('F Y', strtotime("$year-$month-01")); ?></span>
                          <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>"
                            class="btn btn-outline-primary btn-sm">Next &gt;</a>
                        </div>
                      </div>
                      <div class="calendar-grid">
                        <?php
                        $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        $daysOfWeekShort = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
                        foreach ($daysOfWeek as $day) {
                          echo '<div class="calendar-day calendar-day-full">' . $day . '</div>';
                        }
                        foreach ($daysOfWeekShort as $day) {
                          echo '<div class="calendar-day calendar-day-short">' . $day . '</div>';
                        }
                        $firstDayOfMonth = strtotime("$year-$month-01");
                        $totalDays = date('t', $firstDayOfMonth);
                        $startDay = date('w', $firstDayOfMonth);

                        // Fetch all notices for this month
                        $sql = "SELECT NoticeTitle, CreationDate, NoticeMsg FROM tblnotice WHERE MONTH(CreationDate)=:month AND YEAR(CreationDate)=:year ORDER BY CreationDate ASC";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':month', $month, PDO::PARAM_INT);
                        $query->bindParam(':year', $year, PDO::PARAM_INT);
                        $query->execute();
                        $notices = $query->fetchAll(PDO::FETCH_OBJ);

                        // Organize notices by day
                        $noticesByDay = [];
                        foreach ($notices as $notice) {
                          $day = date('j', strtotime($notice->CreationDate));
                          if (!isset($noticesByDay[$day]))
                            $noticesByDay[$day] = [];
                          $noticesByDay[$day][] = $notice;
                        }

                        // Print empty cells before first day
                        for ($i = 0; $i < $startDay; $i++) {
                          echo '<div class="calendar-cell"></div>';
                        }

                        // Print days
                        for ($day = 1; $day <= $totalDays; $day++) {
                          $isToday = (date('Y-m-d') == "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT));
                          echo '<div class="calendar-cell' . ($isToday ? ' today' : '') . '">';
                          echo '<div class="day-number">' . $day . '</div>';
                          if (isset($noticesByDay[$day])) {
                            echo '<div class="notice-list-in-cell">';
                            foreach ($noticesByDay[$day] as $notice) {
                              $title = htmlentities($notice->NoticeTitle);
                              $date = htmlentities($notice->CreationDate);
                              $msg = nl2br(htmlentities($notice->NoticeMsg));
                              echo '<div class="notice-item" onclick="showNoticeDetail(\'' . $title . '\', \'' . $date . '\', \'' . str_replace(array("\r", "\n", "'"), array(" ", "\\n", "\\'"), $msg) . '\')">';
                              echo '<span class="notice-dot"></span><span class="notice-title">' . $title . '</span>';
                              echo '</div>';
                            }
                            echo '</div>';
                          }
                          echo '</div>';
                        }
                        ?>
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
    <!-- Modal for notice detail -->
    <div id="noticeModal">
      <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <div class="modal-title" id="modalTitle"></div>
        <div class="modal-date" id="modalDate"></div>
        <div class="modal-msg" id="modalMsg"></div>
      </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script>
      function showNoticeDetail(title, date, msg) {
        const modal = document.getElementById('noticeModal');
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalDate').innerText = date;
        document.getElementById('modalMsg').innerText = msg;
        modal.style.display = 'block';
      }
      function closeModal() {
        document.getElementById('noticeModal').style.display = 'none';
      }
    </script>
  </body>

  </html>
<?php } ?>