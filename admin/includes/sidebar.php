<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <br><br><br><br>
    <li class="nav-item nav-profile">
      <?php
      $aid = isset($_SESSION['sturecmsaid']) ? $_SESSION['sturecmsaid'] : null;
      if ($aid) {
        $sql = "SELECT * from tbladmin where ID=:aid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':aid', $aid, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);

        if ($query->rowCount() > 0) {
          foreach ($results as $row) {
            $profileImage = !empty($row->Image) ? 'images/' . htmlentities($row->Image) : 'images/faces/face8.jpg';
            ?>
            <a href="profile.php" class="nav-link">
              <div class="profile-image">
                <img class="img-xs rounded-circle" src="<?php echo $profileImage; ?>" alt="profile image">
                <div class="dot-indicator bg-success"></div>
              </div>
              <div class="text-wrapper">
                <p class="profile-name"><?php echo htmlentities($row->AdminName); ?></p>
              </div>
            </a>
          <?php }
        }
      } ?>
    </li>
    <br><br><br>
    <li class="nav-item">
      <a class="nav-link" href="dashboard.php">
        <span class="menu-icon">📊</span>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>

    <!-- Staff (direct link to Manage Staff) -->
    <li class="nav-item">
      <a class="nav-link" href="manage-staff.php">
        <span class="menu-icon">👥</span>
        <span class="menu-title">Staff</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#ui-basic-students" aria-expanded="false"
        aria-controls="ui-basic-students">
        <span class="menu-icon">🎓</span>
        <span class="menu-title">Students</span>
      </a>
      <div class="collapse" id="ui-basic-students">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"><a class="nav-link" href="add-students.php">Add Students</a></li>
          <li class="nav-item"><a class="nav-link" href="manage-students.php">Manage Students</a></li>
          <li class="nav-item"><a class="nav-link" href="validate-achievements.php">Validate Achievements</a></li>
        </ul>
      </div>
    </li>

    <!-- Notice (direct link to Manage Notice) -->
    <li class="nav-item">
      <a class="nav-link" href="manage-notice.php">
        <span class="menu-icon">📌</span>
        <span class="menu-title">Notice</span>
      </a>
    </li>

    <!-- Public Notice (direct link to Manage Public Notice) -->
    <li class="nav-item">
      <a class="nav-link" href="manage-public-notice.php">
        <span class="menu-icon">📣</span>
        <span class="menu-title">Public Notice</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#auth2" aria-expanded="false" aria-controls="auth">
        <span class="menu-icon">📝</span>
        <span class="menu-title">Pages</span>
      </a>
      <div class="collapse" id="auth2">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="about-us.php"> About Us </a></li>
          <li class="nav-item"> <a class="nav-link" href="contact-us.php"> Contact Us </a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="search.php">
        <span class="menu-icon">🔎</span>
        <span class="menu-title">Search</span>
      </a>
    </li>
  </ul>
</nav>