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
  // Handle AJAX request to mark notifications as read
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read_type']) && isset($_POST['id'])) {
    $type = $_POST['mark_read_type'];
    $id = $_POST['id'];
    $stuid = $_SESSION['sturecmsstuid'];
    $update_sql = "";
  
    if ($type === 'achievement') {
      $update_sql = "UPDATE student_achievements SET is_read = 1 WHERE id = :id AND StuID = :stuid";
    } elseif ($type === 'message') {
      $update_sql = "UPDATE tblmessages SET IsRead = 1 WHERE ID = :id AND RecipientStuID = :stuid";
    }
  
    if ($update_sql) {
      $update_query = $dbh->prepare($update_sql);
      $update_query->bindParam(':id', $id, PDO::PARAM_INT);
      $update_query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
      $update_query->execute();
      echo "success";
      exit; // Stop further script execution
    }
    echo "error";
    exit;
  } ?>
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
          // Fetch count of unread rejected achievements
          $rejected_sql = "SELECT COUNT(id) as count FROM student_achievements WHERE StuID = :stuid AND status = 'rejected' AND rejection_reason IS NOT NULL AND is_read = 0";
          $rejected_query = $dbh->prepare($rejected_sql);
          $rejected_query->bindParam(':stuid', $_SESSION['sturecmsstuid'], PDO::PARAM_STR);
          $rejected_query->execute();
          $rejected_count = (int) $rejected_query->fetch(PDO::FETCH_OBJ)->count;

          // Fetch count of unread messages
          $messages_sql = "SELECT COUNT(ID) as count FROM tblmessages WHERE RecipientStuID = :stuid AND IsRead = 0";
          $messages_query = $dbh->prepare($messages_sql);
          $messages_query->bindParam(':stuid', $_SESSION['sturecmsstuid'], PDO::PARAM_STR);
          $messages_query->execute();
          $messages_count = (int) $messages_query->fetch(PDO::FETCH_OBJ)->count;

          // Combine counts for the badge
          $total_notifications = $rejected_count + $messages_count;
          ?>
          <li class="nav-item">
            <a class="nav-link notification-icon-wrapper" onclick="toggleNotificationModal()">
              <span style="font-size: 25px;">🔔</span>
              <?php if ($total_notifications > 0): ?>
                <span class="notification-badge">
                  <?php echo $total_notifications; ?>
                </span>
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
        // Fetch unread messages
        $messages_list_sql = "
            SELECT 
                m.ID, m.Subject, m.Message, m.Timestamp, m.SenderType, m.SenderID, m.IsRead,
                CASE
                    WHEN m.SenderType = 'admin' THEN a.AdminName
                    WHEN m.SenderType = 'staff' THEN s.StaffName
                    ELSE 'System'
                END AS SenderName
    FROM (SELECT * FROM tblmessages WHERE RecipientStuID = :stuid ORDER BY Timestamp DESC LIMIT 10) m
            LEFT JOIN tbladmin a ON m.SenderID = a.ID AND m.SenderType = 'admin'
            LEFT JOIN tblstaff s ON m.SenderID = s.ID AND m.SenderType = 'staff'
            WHERE m.RecipientStuID = :stuid
            ORDER BY m.Timestamp DESC";
        $messages_list_query = $dbh->prepare($messages_list_sql);
        $messages_list_query->bindParam(':stuid', $_SESSION['sturecmsstuid'], PDO::PARAM_STR);
        $messages_list_query->execute();
        $messages = $messages_list_query->fetchAll(PDO::FETCH_OBJ);

        // Fetch unread rejected achievements
        $rejected_achievements_sql = "SELECT id, level, category, rejection_reason, created_at, is_read FROM student_achievements WHERE StuID = :stuid AND status = 'rejected' AND rejection_reason IS NOT NULL ORDER BY created_at DESC LIMIT 10";
        $rejected_achievements_query = $dbh->prepare($rejected_achievements_sql);
        $rejected_achievements_query->bindParam(':stuid', $_SESSION['sturecmsstuid'], PDO::PARAM_STR);
        $rejected_achievements_query->execute();
        $rejected_achievements = $rejected_achievements_query->fetchAll(PDO::FETCH_OBJ);

        if (empty($messages) && empty($rejected_achievements)) {
          echo "<p>No new notifications found.</p>";
        } else {
          // Display new messages
          foreach ($messages as $message) {
            $details_id = 'msg-details-' . $message->ID;
            $is_unread = $message->IsRead == 0;
            ?>
            <div class="notification-item <?php if ($is_unread) echo 'unread-notification'; ?>">
              <div class="notification-item-header">
                <strong class="notification-type">New Message</strong>
                <a href="javascript:void(0);" class="see-more-link" data-read="<?php echo $is_unread ? 'false' : 'true'; ?>"
                  onclick="toggleMessageDetails('<?php echo $details_id; ?>', this, <?php echo $message->ID; ?>)">See
                  more</a>
              </div>
              <div class="notification-summary">
                From: <strong><?php echo htmlentities($message->SenderName); ?></strong><br>
                Subject: <?php echo htmlentities($message->Subject); ?>
              </div>
              <div id="<?php echo $details_id; ?>" class="notification-details" style="display: none;">
                <p class="reason" style="white-space: pre-wrap;"><?php echo htmlentities($message->Message); ?></p>
                <p class="meta">Received on: <?php echo date('F j, Y, g:i a', strtotime($message->Timestamp)); ?></p>
              </div>
            </div>
            <?php
          }

          // Display rejected achievements
          foreach ($rejected_achievements as $achievement) {
            $details_id = 'ach-details-' . $achievement->id;
            $is_unread = $achievement->is_read == 0;
            ?>
            <div class="notification-item <?php if ($is_unread) echo 'unread-notification'; ?>">
              <div class="notification-item-header">
                <strong class="notification-type">Achievement Rejected</strong>
                <a href="javascript:void(0);" class="see-more-link" data-read="<?php echo $is_unread ? 'false' : 'true'; ?>"
                  onclick="toggleAchievementDetails('<?php echo $details_id; ?>', this, <?php echo $achievement->id; ?>)">See
                  more</a>
              </div>
              <div class="notification-summary">
                Your submission for a/an "<?php echo htmlentities($achievement->level); ?>" level achievement in the
                "<?php echo htmlentities($achievement->category); ?>" category was not approved.
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
        }
        ?>
      </div>
    </div>
  </div>

  <style>
    .unread-notification {
      background-color: #f0f8ff; /* A light blue background for unread items */
    }
  </style>

  <script>
    function toggleNotificationModal() {
      var modal = document.getElementById('notificationModal');
      if (modal) modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
    }

    function toggleDetails(id, link) {
      var details = document.getElementById(id);
      if (details) {
        if (details.style.display === 'none') {
          details.style.display = 'block';
          link.textContent = 'See less';
        } else {
          details.style.display = 'none';
          link.textContent = 'See more';
        }
      }
    }

    function markNotificationAsRead(link) {
      if (link.getAttribute('data-read') === 'false') {
        link.setAttribute('data-read', 'true'); // Mark as read
        var badge = document.querySelector('.notification-badge');
        if (badge) {
          var currentCount = parseInt(badge.textContent, 10);
          if (currentCount > 1) {
            badge.textContent = currentCount - 1;
          } else {
            badge.style.display = 'none';
          }
        }
      }
    }

    function toggleAchievementDetails(elementId, link, achievementId) {
      toggleDetails(elementId, link);
      if (link.getAttribute('data-read') === 'false') {
        link.closest('.notification-item').classList.remove('unread-notification');
        markNotificationAsRead(link);
        // AJAX call to mark achievement as read
        fetch(window.location.pathname, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'mark_read_type=achievement&id=' + achievementId
        });
      }
    }

    function toggleMessageDetails(elementId, link, messageId) {
      toggleDetails(elementId, link);
      if (link.getAttribute('data-read') === 'false') {
        link.closest('.notification-item').classList.remove('unread-notification');
        markNotificationAsRead(link);
        // AJAX call to mark message as read
        fetch(window.location.pathname, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'mark_read_type=message&id=' + messageId
        });
      }
    }
    // Close modal if user clicks outside of it
    window.onclick = function (event) { if (event.target == document.getElementById('notificationModal')) { toggleNotificationModal(); } }
  </script>
</nav>