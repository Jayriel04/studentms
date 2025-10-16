<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <?php
        $uid = $_SESSION['sturecmsstuid'];
        $sql = "SELECT * FROM tblstudent WHERE StuID=:uid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':uid', $uid, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);

        if ($query->rowCount() > 0) {
          foreach ($results as $row) {
            $profileImg = !empty($row->Image) ? "../admin/images/" . $row->Image : "images/faces/face8.jpg";
            ?>
            <div class="profile-image">
              <img class="img-xs rounded-circle" src="<?php echo $profileImg; ?>" alt="profile image">
              <div class="dot-indicator bg-success"></div>
            </div>
            <div class="text-wrapper">
              <p class="profile-name"><?php echo htmlentities($row->FirstName . " " . $row->FamilyName); ?></p>
            </div>
          <?php }
        } ?>
      </a>
    </li>
    <br><br><br><br><br>
    <li class="nav-item">
      <a class="nav-link" href="dashboard.php">
        <span class="menu-icon">📊</span>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="view-notice.php">
        <span class="menu-icon">🔔</span>
        <span class="menu-title">View Notice</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="student-profile.php">
        <span class="menu-icon">👤</span>
        <span class="menu-title" style="padding-left: 4px;">View Profile</span>
      </a>
    </li>
  </ul>
</nav>