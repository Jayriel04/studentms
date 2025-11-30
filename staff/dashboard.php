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

                <!-- Total Public Notices -->
                <a href="manage-public-notice.php" class="stat-card">
                  <div class="stat-header">
                    <div class="stat-icon">📢</div>
                    <div class="menu-dots"></div>
                  </div>
                  <div class="stat-label">Public Notices</div>
                  <div class="stat-value-row">
                    <?php
                    $sql_notices = "SELECT COUNT(ID) FROM tblpublicnotice";
                    $q_notices = $dbh->query($sql_notices);
                    $totpublicnotice = $q_notices->fetchColumn();
                    ?>
                    <div class="stat-value"><?php echo htmlentities($totpublicnotice); ?></div>
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
                  <div class="mt-3 text-center">
                    <a href="manage-notice.php" class="btn btn-outline-primary btn-sm">View All Notices</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
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
    <script src="vendors/chart.js/Chart.min.js"></script>
    <script src="js/chart.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/script.js"></script>
    <script>
      function showNoticeDetail(title, date, msg) {
        const modal = document.getElementById('noticeModal');
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalDate').innerText = date;
        document.getElementById('modalMsg').innerHTML = msg.replace(/\\n/g, '<br>');
        modal.style.display = 'block';
      }
      function closeModal() {
        document.getElementById('noticeModal').style.display = 'none';
      }

      // Render the skills chart as a bar chart
      document.addEventListener('DOMContentLoaded', function () {
        var yearLevelChartCanvas = document.getElementById('yearLevelChart');
        if (yearLevelChartCanvas) {
          var yearLevelData = JSON.parse(yearLevelChartCanvas.getAttribute('data-year-levels'));
          var labels = yearLevelData.map(function (item) { return item.name; });
          var data = yearLevelData.map(function (item) { return item.student_count; });

          new Chart(yearLevelChartCanvas, {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [{
                label: 'Number of Students',
                data: data,
                backgroundColor: [
                  'rgba(75, 192, 192, 0.6)',
                  'rgba(54, 162, 235, 0.6)',
                  'rgba(255, 206, 86, 0.6)',
                  'rgba(255, 99, 132, 0.6)'
                ],
                borderColor: [
                  'rgba(75, 192, 192, 1)',
                  'rgba(54, 162, 235, 1)',
                  'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
              }]
            },
            options: {
              responsive: true,
              plugins: { legend: { display: false }, title: { display: true, text: 'Student Distribution by Year Level' } },
              scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
              }
            }
          });
        }
      });
    </script>
  </body>

  </html>
<?php } ?>