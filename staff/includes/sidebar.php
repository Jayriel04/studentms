<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item nav-profile">
      <?php
      $sid = $_SESSION['sturecmsstaffid'];
      $sql = "SELECT * from tblstaff where ID=:sid";
      $query = $dbh->prepare($sql);
      $query->bindParam(':sid', $sid, PDO::PARAM_STR);
      $query->execute();
      $results = $query->fetchAll(PDO::FETCH_OBJ);
      if ($query->rowCount() > 0) {
        foreach ($results as $row) {
          $profileImg = !empty($row->Image) ? "../admin/images/" . htmlentities($row->Image) : "images/faces/face8.jpg";
          ?>
          <a href="profile.php" class="nav-link">
            <div class="profile-image">
              <img class="img-xs rounded-circle" src="<?php echo $profileImg; ?>" alt="profile image">
              <div class="dot-indicator bg-success"></div>
            </div>
            <div class="text-wrapper">
              <p class="profile-name"><?php echo htmlentities($row->StaffName); ?></p>
              <p class="designation"><?php echo htmlentities($row->Email); ?></p>
            </div>
          </a>
        <?php }
      } ?>
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
      <a class="nav-link" data-toggle="collapse" href="#ui-basic1" aria-expanded="false" aria-controls="ui-basic1">
        <span class="menu-title">Students</span>
        <i class="icon-people menu-icon"></i>
      </a>
      <div class="collapse" id="ui-basic1">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="add-students.php">Add Students</a></li>
          <li class="nav-item"> <a class="nav-link" href="manage-students.php">Manage Students</a></li>
          <li class="nav-item"> <a class="nav-link" href="validate-achievements.php">Validate Achievements</a></li>
        </ul>
      </div>
    </li>
    
    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#notice" aria-expanded="false" aria-controls="notice">
        <span class="menu-title">Notice</span>
        <i class="icon-doc menu-icon"></i>
      </a>
      <div class="collapse" id="notice">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="add-notice.php"> Add Notice </a></li>
          <li class="nav-item"> <a class="nav-link" href="manage-notice.php"> Manage Notice </a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#publicnotice" aria-expanded="false"
        aria-controls="publicnotice">
        <span class="menu-title">Public Notice</span>
        <i class="icon-doc menu-icon"></i>
      </a>
      <div class="collapse" id="publicnotice">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="add-public-notice.php"> Add Public Notice </a></li>
          <li class="nav-item"> <a class="nav-link" href="manage-public-notice.php"> Manage Public Notice </a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#pages" aria-expanded="false" aria-controls="pages">
        <span class="menu-title">Pages</span>
        <i class="icon-doc menu-icon"></i>
      </a>
      <div class="collapse" id="pages">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="about-us.php"> About Us </a></li>
          <li class="nav-item"> <a class="nav-link" href="contact-us.php"> Contact Us </a></li>
        </ul>
      </div>
    </li>
    <!-- <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#reports" aria-expanded="false" aria-controls="reports">
        <span class="menu-title">Reports</span>
        <i class="icon-doc menu-icon"></i>
      </a>
      <div class="collapse" id="reports">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="between-dates-reports.php"> Student Reports </a></li>
        </ul>
      </div>
    </li> -->
    <li class="nav-item">
      <a class="nav-link" href="search.php">
        <span class="menu-title">Search</span>
        <i class="icon-magnifier menu-icon"></i>
      </a>
    </li>
  </ul>
</nav>