<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
  <div class="navbar-brand-wrapper d-none d-lg-flex align-items-center">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <a class="navbar-brand brand-logo" href="dashboard.php">
      <strong style="color: white;">SPS</strong>
    </a>
    <a class="navbar-brand brand-logo-mini" href="dashboard.php">
      <strong style="color: white;">SPS</strong>
    </a>
  </div>
  <?php
  $uid = $_SESSION['sturecmsuid'];
  $sql = "SELECT * FROM tblstudent WHERE ID=:uid";
  $query = $dbh->prepare($sql);
  $query->bindParam(':uid', $uid, PDO::PARAM_STR);
  $query->execute();
  $results = $query->fetchAll(PDO::FETCH_OBJ);

  if ($query->rowCount() > 0) {
    foreach ($results as $row) {
      // Use user's profile image if exists, else fallback
      $profileImg = !empty($row->Image) ? "../admin/images/" . $row->Image : "images/faces/face8.jpg";
  ?>
      <div class="navbar-menu-wrapper d-flex align-items-center flex-grow-1">
        <h5 class="mb-0 font-weight-medium d-none d-lg-flex">
          Welcome to the dashboard!
        </h5>
        <ul class="navbar-nav navbar-nav-right ml-auto">
          <?php
            // Fetch count of rejected achievements with reasons for the logged-in student
            $rejected_sql = "SELECT COUNT(id) as count FROM student_achievements WHERE StuID = :stuid AND status = 'rejected' AND rejection_reason IS NOT NULL";
            $rejected_query = $dbh->prepare($rejected_sql);
            $rejected_query->bindParam(':stuid', $_SESSION['sturecmsstuid'], PDO::PARAM_STR);
            $rejected_query->execute();
            $rejected_count = $rejected_query->fetch(PDO::FETCH_OBJ)->count;
          ?>
          <li class="nav-item">
            <a class="nav-link notification-icon-wrapper" onclick="toggleNotificationModal()">
              <span style="font-size: 25px;">🔔</span>
              <?php if ($rejected_count > 0): ?>
                <span class="notification-badge"><?php echo $rejected_count; ?></span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item dropdown user-dropdown">
            <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
              <img class="img-xs rounded-circle ml-2" src="<?php echo $profileImg; ?>" alt="Profile image">
              <span class="font-weight-normal"> <?php echo htmlentities($row->FirstName . " " . $row->FamilyName); ?> </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown" style="min-width:220px;">
              <div class="dropdown-header text-center p-2">
                <img class="img-md rounded-circle mb-2" src="<?php echo $profileImg; ?>" alt="Profile image" style="width:60px;height:60px;object-fit:cover;">
                <p class="mb-1 mt-1" style="font-size:1rem;"><?php echo htmlentities($row->FirstName . " " . $row->FamilyName); ?></p>
                <p class="font-weight-light text-muted mb-0" style="font-size:0.9rem;"><?php echo htmlentities($row->EmailAddress); ?></p>
              </div>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="student-profile.php"><i class="dropdown-item-icon icon-user text-primary"></i> My Profile</a>
              <a class="dropdown-item" href="change-password.php"><i class="dropdown-item-icon icon-energy text-primary"></i> Change Password</a>
              <a class="dropdown-item" href="logout.php"><i class="dropdown-item-icon icon-power text-primary"></i>Sign Out</a>
            </div>
          </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
          <span class="icon-menu"></span>
        </button>
      </div>
  <?php
    }
  }
  ?>

  <!-- Notification Modal -->
  <div id="notificationModal" class="notification-modal">
    <div class="notification-modal-content">
      <div class="notification-modal-header">
        <h5 class="notification-modal-title">Notifications</h5>
        <span class="close-notification-modal" onclick="toggleNotificationModal()">&times;</span>
      </div>
      <div class="notification-modal-body">
        <?php
          $rejected_achievements_sql = "SELECT level, category, status, rejection_reason, created_at FROM student_achievements WHERE StuID = :stuid AND status = 'rejected' AND rejection_reason IS NOT NULL ORDER BY created_at DESC";
          $rejected_achievements_query = $dbh->prepare($rejected_achievements_sql);
          $rejected_achievements_query->bindParam(':stuid', $_SESSION['sturecmsstuid'], PDO::PARAM_STR);
          $rejected_achievements_query->execute();
          $rejected_achievements = $rejected_achievements_query->fetchAll(PDO::FETCH_OBJ);

          if ($rejected_achievements_query->rowCount() > 0) {
            foreach ($rejected_achievements as $achievement) {
              // Create a unique ID for each collapsible element
              $details_id = 'details-' . uniqid();
        ?>
              <div class="notification-item">
                <div class="notification-item-header">
                  <strong class="notification-type">Achievement Rejected</strong>
                  <a href="javascript:void(0);" class="see-more-link" data-read="false" onclick="toggleNotificationDetails('<?php echo $details_id; ?>', this)">See more</a>
                </div>
                <div class="notification-summary">
                  Your submission for a/an "<?php echo htmlentities($achievement->level); ?>" level achievement in the "<?php echo htmlentities($achievement->category); ?>" category was not approved.
                </div>
                <div id="<?php echo $details_id; ?>" class="notification-details" style="display: none;">
                  <p class="reason">
                    <strong>Rejection Reason:</strong>
                    <?php echo htmlentities($achievement->rejection_reason); ?>
                  </p>
                  <p class="meta">Submitted on: <?php echo date('F j, Y, g:i a', strtotime($achievement->created_at)); ?></p>
                </div>
              </div>
        <?php
            }
          } else {
            echo "<p>No notifications found.</p>";
          }
        ?>
      </div>
    </div>
  </div>

  <script>
    function toggleNotificationModal() {
      var modal = document.getElementById('notificationModal');
      if (modal) modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
    }

    function toggleNotificationDetails(id, link) {
      var details = document.getElementById(id);
      if (details) {
        // Check if the notification has been read before
        if (link.getAttribute('data-read') === 'false') {
          link.setAttribute('data-read', 'true'); // Mark as read

          // Decrement the badge count
          var badge = document.querySelector('.notification-badge');
          if (badge) {
            var currentCount = parseInt(badge.textContent, 10);
            if (currentCount > 1) {
              badge.textContent = currentCount - 1;
            } else {
              badge.style.display = 'none'; // Hide badge when count is zero
            }
          }
        }
        if (details.style.display === 'none') {
          details.style.display = 'block';
          link.textContent = 'See less';
        } else {
          details.style.display = 'none';
          link.textContent = 'See more';
        }
      }
    }
    // Close modal if user clicks outside of it
    window.onclick = function(event) { if (event.target == document.getElementById('notificationModal')) { toggleNotificationModal(); } }
  </script>
</nav>