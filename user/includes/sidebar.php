<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="profile-image">
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
              <img class="img-xs rounded-circle" src="<?php echo $profileImg; ?>" alt="profile image">
              <div class="dot-indicator bg-success"></div>
        </div>
        <div class="text-wrapper">
          <p class="profile-name" style="color: #fff;"><?php echo htmlentities($row->FirstName . " " . $row->FamilyName); ?></p>
          <p class="designation"><?php echo htmlentities($row->EmailAddress); ?></p>
        </div>
          <?php
            }
          }
          ?>
      </a>
    </li>
    <li class="nav-item nav-category">
      <span class="nav-link">Dashboard</span>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="dashboard.php">
        <span class="menu-title">Dashboard</span>
        <i class="icon-screen-desktop menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="view-notice.php">
        <span class="menu-title">View Notice</span>
        <i class="icon-doc menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="student-profile.php">
        <span class="menu-title">View Profile</span>
        <i class="icon-user menu-icon"></i>
      </a>
    </li>
  </ul>
</nav>