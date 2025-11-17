<?php
session_start();
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstuid'] == 0)) {
  header('location:logout.php');
} else {
  // Fetch student's name for the welcome banner
  $stuid = $_SESSION['sturecmsstuid'];
  $sql = "SELECT FirstName FROM tblstudent WHERE StuID=:stuid";
  $query = $dbh->prepare($sql);
  $query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
  $query->execute();
  $results = $query->fetchAll(PDO::FETCH_OBJ);
  $studentName = "Student"; // Default name
  if ($query->rowCount() > 0) {
    $studentName = htmlentities($results[0]->FirstName);
  }

  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Student Management System|||Dashboard</title>
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
            <div class="welcome-banner">
              <p style="font-size: 12px; margin-bottom: 5px;"><?php echo date('F j, Y'); ?></p>
              <h2>Welcome back, <?php echo $studentName; ?>!</h2>
              <p>Always stay updated in your student portal</p>
            </div>

            <div class="content-grid">
              <!-- Quick Actions Section -->
              <div class="courses-section">
                <div class="section-header">
                  <div class="section-title" style="margin: 0;">Quick Actions</div>
                </div>
                <div class="courses-grid">
                  <div class="course-card">
                    <h4>Update Profile</h4>
                    <div class="course-icon">✍🏻</div>
                    <a href="update-profile.php" class="view-btn">Update</a>
                  </div>
                  <div class="course-card">
                    <h4>Add Skills</h4>
                    <div class="course-icon">⛹️</div>
                    <a href="add-achievement.php" class="view-btn">Add</a>
                  </div>
                </div>
              </div>

              <!-- Daily Notice Section -->
              <div class="notices-section">
                <div class="section-header">
                  <div class="section-title" style="margin: 0;">Daily Notice</div>
                  <a href="view-notice.php" class="see-all">See all</a>
                </div>
                <?php
                $sql = "SELECT NoticeTitle, NoticeMsg, CreationDate FROM tblnotice ORDER BY CreationDate DESC LIMIT 3";
                $query = $dbh->prepare($sql);
                $query->execute();
                $notices = $query->fetchAll(PDO::FETCH_OBJ);

                if ($query->rowCount() > 0) {
                  foreach ($notices as $notice) {
                    ?>
                    <div class="notice-item">
                      <div class="notice-title"><?php echo htmlentities($notice->NoticeTitle); ?></div>
                      <div class="notice-text"><?php echo substr(htmlentities($notice->NoticeMsg), 0, 120); ?>...</div>
                      <a href="view-notice.php" class="see-more">See more</a>
                    </div>
                    <?php
                  }
                } else {
                  echo '<div class="notice-text">No new notices at the moment.</div>';
                }
                ?>
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

  </html><?php } ?>