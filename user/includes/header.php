<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
  <div class="navbar-brand-wrapper d-none d-lg-flex align-items-center" style="background: #ffffff;">
    <a class="navbar-brand brand-logo" href="dashboard.php" style="display: flex; align-items: center; gap: 10px;">
      <img src="../admin/images/logo/logo.jpg" alt="logo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" />
      <strong style="color: #333;">SPS</strong>
    </a>
    <a class="navbar-brand brand-logo-mini" href="dashboard.php"><img src="../admin/images/logo/logo.jpg" alt="logo" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;" /></a>
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
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas" data-target="#sidebar">
          <span class="icon-menu"></span>
        </button>
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
          <li class="nav-item" style="position: relative;">
            <a href="javascript:void(0)" id="notifIcon" class="nav-link notification-icon-wrapper" aria-haspopup="true"
              aria-expanded="false">
              <i class="icon-bell" style="font-size: 20px;"></i>
              <?php if ($total_notifications > 0): ?>
                <span class="notification-badge" id="notifBadge">
                  <?php echo $total_notifications; ?>
                </span>
              <?php else: ?>
                <span class="notification-badge" id="notifBadge" style="display:none;"></span>
              <?php endif; ?>
            </a>

            <!-- Notification Panel (hidden by default) -->
            <div id="notifPanel" class="notification-panel" role="dialog" aria-label="Notifications" aria-hidden="true"></div>
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
              <a class="dropdown-item" href="student-profile.php"><span class="dropdown-item-icon">👤</span> My Profile</a>
              <a class="dropdown-item" href="change-password.php"><span class="dropdown-item-icon">🔑</span> Change Password</a>
              <a class="dropdown-item" href="logout.php"><span class="dropdown-item-icon">🚪</span> Sign Out</a>
            </div>
          </li>
        </ul>
      </div>
  <?php
    }
  }
  ?>

  <script>
    (function () {
      const notifIcon = document.getElementById('notifIcon');
      const notifPanel = document.getElementById('notifPanel');
      const notifBadge = document.getElementById('notifBadge');
      let panelVisible = false;
      let notificationsLoaded = false;

      function openPanel() {
        if (!notifPanel) return;
        notifPanel.classList.add('show');
        notifPanel.setAttribute('aria-hidden', 'false');
        notifIcon.setAttribute('aria-expanded', 'true');
        panelVisible = true;
        if (!notificationsLoaded) {
          fetchNotifications();
        }
      }

      function closePanel() {
        if (!notifPanel) return;
        notifPanel.classList.remove('show');
        notifPanel.setAttribute('aria-hidden', 'true');
        notifIcon.setAttribute('aria-expanded', 'false');
        panelVisible = false;
      }

      function togglePanel() {
        panelVisible ? closePanel() : openPanel();
      }

      function fetchNotifications() {
        notifPanel.innerHTML = '<div class="notif-empty">Loading...</div>';
        // We use the existing PHP logic by fetching the current page with a special parameter
        fetch(window.location.pathname + '?get_notifications=1')
          .then(response => response.text())
          .then(html => {
            notifPanel.innerHTML = html;
            notificationsLoaded = true;

            // Attach tab-switching logic AFTER content is loaded
            notifPanel.querySelectorAll('.tab-link').forEach(button => {
              button.addEventListener('click', function(event) {
                event.preventDefault();
                const tabId = this.getAttribute('data-tab');

                // Deactivate all tabs and content
                notifPanel.querySelectorAll('.tab-link').forEach(tab => tab.classList.remove('active'));
                notifPanel.querySelectorAll('.notif-tab-content').forEach(content => content.classList.remove('active'));

                // Activate the clicked tab and its content
                this.classList.add('active');
                const activeContent = notifPanel.querySelector('#notif-tab-' + tabId);
                if (activeContent) activeContent.classList.add('active');
              });
            });
          }).catch(() => {
            notifPanel.innerHTML = '<div class="notif-empty">Could not load notifications.</div>';
          });
      }

      function markAllAsRead() {
        // This AJAX call will mark all notifications as read.
      }

      if (notifIcon) {
        notifIcon.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          togglePanel();
        });
      }

      // Close on outside click
      document.addEventListener('click', function (e) {
        if (!panelVisible) return;
        if (notifPanel && !notifPanel.contains(e.target) && notifIcon && !notifIcon.contains(e.target)) {
          closePanel();
        }
      });

      // Close on Escape key
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panelVisible) {
          closePanel();
        }
      });
    })();

    // Delegated event listener for "See more" links
    document.addEventListener('click', function (event) {
      const target = event.target;
      if (target && target.classList.contains('see-more-link')) {
        event.preventDefault();
        const detailsId = target.getAttribute('data-details-id');
        const detailsElement = document.getElementById(detailsId);

        if (detailsElement) {
          const isVisible = detailsElement.style.display === 'block';
          detailsElement.style.display = isVisible ? 'none' : 'block';
          target.textContent = isVisible ? 'See more' : 'See less';

          // Mark as read if it's the first time clicking "See more"
          if (!isVisible && target.getAttribute('data-read') === 'false') {
            target.setAttribute('data-read', 'true'); // Mark as read on the frontend
            target.closest('.notif-item').classList.remove('unread-notification');

            const notificationType = target.getAttribute('data-type');
            const notificationId = target.getAttribute('data-id');

            // AJAX call to mark as read on the backend
            fetch(window.location.pathname, {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: `mark_read_type=${notificationType}&id=${notificationId}`
            });

            // Decrement badge count
            const badge = document.getElementById('notifBadge');
            const currentCount = parseInt(badge.textContent, 10);
            badge.textContent = currentCount > 1 ? currentCount - 1 : '';
            if (currentCount <= 1) badge.style.display = 'none';
          }
        }
      }
    });
  </script>
</nav>
<?php
// This block will execute when the fetch request with `get_notifications=1` is made.
if (isset($_GET['get_notifications'])) {
  ob_clean(); // Clear any previous output
  $stuid = $_SESSION['sturecmsstuid'];
  
  // Unified query to fetch all notifications
  $notifications_sql = "
      (SELECT 'message' as type, m.ID as id, m.Subject as title, m.Message as body, m.Timestamp as timestamp, m.IsRead as is_read, s.StaffName as sender_name, NULL as level, NULL as category 
       FROM tblmessages m JOIN tblstaff s ON m.SenderID = s.ID AND m.SenderType = 'staff' 
       WHERE m.RecipientStuID = :stuid)
      UNION ALL
      (SELECT 'message' as type, m.ID as id, m.Subject as title, m.Message as body, m.Timestamp as timestamp, m.IsRead as is_read, a.AdminName as sender_name, NULL as level, NULL as category 
       FROM tblmessages m JOIN tbladmin a ON m.SenderID = a.ID AND m.SenderType = 'admin' 
       WHERE m.RecipientStuID = :stuid)
      UNION ALL
      (SELECT 'achievement' as type, sa.id as id, 'Achievement Rejected' as title, sa.rejection_reason as body, sa.created_at as timestamp, sa.is_read as is_read, NULL as sender_name, sa.level, sa.category 
       FROM student_achievements sa 
       WHERE sa.StuID = :stuid AND sa.status = 'rejected' AND sa.rejection_reason IS NOT NULL)
      ORDER BY timestamp DESC LIMIT 50";

  $notifications_query = $dbh->prepare($notifications_sql);
  $notifications_query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
  $notifications_query->execute();
  $notifications = $notifications_query->fetchAll(PDO::FETCH_OBJ);

  // Helper function to group notifications
  function get_time_ago_group($time) {
      $time_difference = time() - strtotime($time);
      if ($time_difference < 86400) { // 24 hours
          return 'Today';
      } elseif ($time_difference < 604800) { // 7 days
          return 'This Week';
      } else {
          return 'Older';
      }
  }

  $grouped_notifications = [
      'Today' => [],
      'This Week' => [],
      'Older' => []
  ];

  $unread_notifications = [];
  foreach ($notifications as $notification) {
      $group = get_time_ago_group($notification->timestamp);
      $grouped_notifications[$group][] = $notification;
      if ($notification->is_read == 0) {
        $unread_notifications[] = $notification;
      }
  }

  $grouped_unread_notifications = [
      'Today' => [],
      'This Week' => [],
      'Older' => []
  ];
  foreach ($unread_notifications as $notification) {
      $group = get_time_ago_group($notification->timestamp);
      $grouped_unread_notifications[$group][] = $notification;
  }

  echo '<div class="panel-header"><span>Notifications</span></div>';
  echo '<div class="panel-body">';

  if (empty($notifications)) {
    echo '<div class="notif-empty">No new notifications.</div>';
  } else {
    echo '<div class="notification-tabs">
            <button class="tab-link active" data-tab="all">All</button>
            <button class="tab-link" data-tab="unread">Unread</button>
          </div>';
    
    echo '<div class="notif-tab-content active" id="notif-tab-all">';
      foreach ($grouped_notifications as $group_name => $group_items) {
          if (empty($group_items)) continue;
          echo '<div class="notif-group-header">' . $group_name . '</div>';
          foreach ($group_items as $item) {
              $is_unread = $item->is_read == 0;
              echo '<div class="notif-item ' . ($is_unread ? 'unread-notification' : '') . '">';
              if ($item->type === 'achievement') {
                  $details_id = 'ach-details-' . $item->id;
                  echo '<div class="icon-wrapper ach-icon"><i class="icon-close"></i></div>';
                  echo '<div class="msg-content">';
                  echo '<div class="msg-header">Achievement Rejected <a href="#" class="see-more-link" data-details-id="' . $details_id . '" data-id="' . $item->id . '" data-type="achievement" data-read="' . ($is_unread ? 'false' : 'true') . '">See more</a></div>';
                  echo '<div class="msg-summary">Your ' . htmlentities($item->level) . ' achievement in ' . htmlentities($item->category) . ' was not approved.</div>';
                  echo '<div class="notification-details" id="' . $details_id . '" style="display:none;"><p class="reason"><strong>Reason:</strong> ' . htmlentities($item->body) . '</p></div>';
                  echo '<div class="msg-time">' . date('F j, Y, g:i a', strtotime($item->timestamp)) . '</div>';
                  echo '</div>';
              } else { // message
                  $details_id = 'msg-details-' . $item->id;
                  echo '<div class="icon-wrapper msg-icon"><i class="icon-envelope"></i></div>';
                  echo '<div class="msg-content">';
                  echo '<div class="msg-header">New Message from ' . htmlentities($item->sender_name) . ' <a href="#" class="see-more-link" data-details-id="' . $details_id . '" data-id="' . $item->id . '" data-type="message" data-read="' . ($is_unread ? 'false' : 'true') . '">See more</a></div>';
                  echo '<div class="msg-summary">Subject: ' . htmlentities($item->title) . '</div>';
                  echo '<div class="notification-details" id="' . $details_id . '" style="display:none;"><p>' . nl2br(htmlentities($item->body)) . '</p></div>';
                  echo '<div class="msg-time">' . date('F j, Y, g:i a', strtotime($item->timestamp)) . '</div>';
                  echo '</div>';
              }
              echo '</div>';
          }
      }
    echo '</div>';

    echo '<div class="notif-tab-content" id="notif-tab-unread">';
      if(empty($unread_notifications)) {
        echo '<div class="notif-empty">No unread notifications.</div>';
      } else {
        foreach ($grouped_unread_notifications as $group_name => $group_items) {
            if (empty($group_items)) continue;
            echo '<div class="notif-group-header">' . $group_name . '</div>';
            foreach ($group_items as $item) {
                $is_unread = $item->is_read == 0;
                echo '<div class="notif-item ' . ($is_unread ? 'unread-notification' : '') . '">';
                if ($item->type === 'achievement') {
                    $details_id = 'ach-details-unread-' . $item->id;
                    echo '<div class="icon-wrapper ach-icon"><i class="icon-close"></i></div>';
                    echo '<div class="msg-content">';
                    echo '<div class="msg-header">Achievement Rejected <a href="#" class="see-more-link" data-details-id="' . $details_id . '" data-id="' . $item->id . '" data-type="achievement" data-read="' . ($is_unread ? 'false' : 'true') . '">See more</a></div>';
                    echo '<div class="msg-summary">Your ' . htmlentities($item->level) . ' achievement in ' . htmlentities($item->category) . ' was not approved.</div>';
                    echo '<div class="notification-details" id="' . $details_id . '" style="display:none;"><p class="reason"><strong>Reason:</strong> ' . htmlentities($item->body) . '</p></div>';
                    echo '<div class="msg-time">' . date('F j, Y, g:i a', strtotime($item->timestamp)) . '</div>';
                    echo '</div>';
                } else { // message
                    $details_id = 'msg-details-unread-' . $item->id;
                    echo '<div class="icon-wrapper msg-icon"><i class="icon-envelope"></i></div>';
                    echo '<div class="msg-content">';
                    echo '<div class="msg-header">New Message from ' . htmlentities($item->sender_name) . ' <a href="#" class="see-more-link" data-details-id="' . $details_id . '" data-id="' . $item->id . '" data-type="message" data-read="' . ($is_unread ? 'false' : 'true') . '">See more</a></div>';
                    echo '<div class="msg-summary">Subject: ' . htmlentities($item->title) . '</div>';
                    echo '<div class="notification-details" id="' . $details_id . '" style="display:none;"><p>' . nl2br(htmlentities($item->body)) . '</p></div>';
                    echo '<div class="msg-time">' . date('F j, Y, g:i a', strtotime($item->timestamp)) . '</div>';
                    echo '</div>';
                }
                echo '</div>';
            }
        }
      }
    echo '</div>';
  }
  echo '</div>';
  exit; // Stop execution after sending the panel content
}

// This block handles marking all notifications as read
?>