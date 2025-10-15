 <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="navbar-brand-wrapper d-none d-lg-flex align-items-center">
          <a class="navbar-brand brand-logo" href="dashboard.php">
            <strong style="color: white;">SPS</strong>
          </a>
          <a class="navbar-brand brand-logo-mini" href="dashboard.php"><img src="images/logo-mini.svg" alt="logo" /></a>
        </div><?php
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
        <div class="navbar-menu-wrapper d-flex align-items-center flex-grow-1">
          <h5 class="mb-0 font-weight-medium d-none d-lg-flex"><?php  echo htmlentities($row->AdminName);?> Welcome dashboard!</h5>
          <ul class="navbar-nav navbar-nav-right ml-auto">
            <li class="nav-item dropdown user-dropdown">
              <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
                <img class="img-xs rounded-circle ml-2" src="<?php echo $profileImage; ?>" alt="Profile image"> <span class="font-weight-normal"> <?php  echo htmlentities($row->AdminName);?> </span></a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                <div class="dropdown-header text-center">
                  <img class="img-md rounded-circle" src="<?php echo $profileImage; ?>" alt="Profile image" style="width: 60px; height: 60px; object-fit: cover;">
                  <p class="mb-1 mt-3"><?php  echo htmlentities($row->AdminName);?></p>
                  <p class="font-weight-light text-muted mb-0"><?php  echo htmlentities($row->Email);?></p>
                </div><?php }
          }
        } ?>
                <a class="dropdown-item" href="profile.php"><i class="dropdown-item-icon icon-user text-primary"></i> My Profile</a>
                <a class="dropdown-item" href="change-password.php"><i class="dropdown-item-icon icon-energy text-primary"></i> Setting</a>
                <a class="dropdown-item" href="logout.php"><i class="dropdown-item-icon icon-power text-primary"></i>Sign Out</a>
              </div>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="icon-menu"></span>
          </button>
        </div>
      </nav>