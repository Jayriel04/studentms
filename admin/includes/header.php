 <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
  <?php
  $aid = isset($_SESSION['sturecmsaid']) ? $_SESSION['sturecmsaid'] : null;
  $adminName = 'Admin'; // Default name
  $adminEmail = ''; // Default email
  $profileImage = 'images/faces/face8.jpg'; // Default image

  if ($aid) {
    $sql = "SELECT * from tbladmin where ID=:aid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':aid', $aid, PDO::PARAM_STR);
    $query->execute();
    $adminData = $query->fetch(PDO::FETCH_OBJ);

    if ($adminData) {
      $adminName = $adminData->AdminName;
      $adminEmail = $adminData->Email;
      $profileImage = !empty($adminData->Image) ? 'images/' . htmlentities($adminData->Image) : 'images/faces/face8.jpg';
    }
  }
  ?>
  <div class="navbar-brand-wrapper d-none d-lg-flex align-items-center" style="background: #ffffff;">
    <a class="navbar-brand brand-logo" href="dashboard.php" style="display: flex; align-items: center; gap: 10px;">
      <img src="<?php echo $profileImage; ?>" alt="logo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" />
      <strong style="color: #333;">SPS</strong>
    </a>
    <a class="navbar-brand brand-logo-mini" href="dashboard.php"><img src="<?php echo $profileImage; ?>" alt="logo" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;" /></a>
  </div>
  <div class="navbar-menu-wrapper d-flex align-items-center flex-grow-1">
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas" data-target="#sidebar">
      <span class="icon-menu"></span>
    </button>
    <h5 class="mb-0 font-weight-medium d-none d-lg-flex"><?php echo htmlentities($adminName); ?> Welcome to dashboard!</h5>
    <ul class="navbar-nav navbar-nav-right ml-auto">
      <li class="nav-item dropdown user-dropdown">
        <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
          <img class="img-xs rounded-circle ml-2" src="<?php echo $profileImage; ?>" alt="Profile image"> <span class="font-weight-normal"> <?php echo htmlentities($adminName); ?> </span></a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
          <div class="dropdown-header text-center">
            <img class="img-md rounded-circle" src="<?php echo $profileImage; ?>" alt="Profile image" style="width: 60px; height: 60px; object-fit: cover;">
            <p class="mb-1 mt-3"><?php echo htmlentities($adminName); ?></p>
            <p class="font-weight-light text-muted mb-0"><?php echo htmlentities($adminEmail); ?></p>
          </div>
          <a class="dropdown-item" href="profile.php"><i class="dropdown-item-icon icon-user text-primary"></i> My Profile</a>
          <a class="dropdown-item" href="change-password.php"><i class="dropdown-item-icon icon-energy text-primary"></i> Change Password</a>
          <a class="dropdown-item" href="logout.php"><i class="dropdown-item-icon icon-power text-primary"></i>Sign Out</a>
        </div>
      </li>
    </ul>
  </div>
</nav>