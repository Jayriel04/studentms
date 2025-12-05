<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <?php
  // The active page is determined for styling the active menu item.
  $current_page = basename($_SERVER['PHP_SELF']);
  ?>
  <div class="menu">
    <a href="dashboard.php" class="menu-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
      <span class="menu-item-icon">📊</span>
      <span class="menu-item-text">Dashboard</span>
    </a>

    <a href="manage-staff.php" class="menu-item <?php echo ($current_page == 'manage-staff.php' || $current_page == 'add-staff.php' || $current_page == 'edit-staff-detail.php') ? 'active' : ''; ?>">
      <span class="menu-item-icon">👥</span>
      <span class="menu-item-text">Staff</span>
    </a>

    <a href="manage-students.php" class="menu-item <?php echo in_array($current_page, ['add-students.php', 'manage-students.php', 'validate-achievements.php', 'edit-student-detail.php', 'view-student-profile.php']) ? 'active' : ''; ?>">
      <span class="menu-item-icon">🎓</span>
      <span class="menu-item-text">Students</span>
    </a>

    <a href="manage-notice.php" class="menu-item <?php echo ($current_page == 'manage-notice.php' || $current_page == 'add-notice.php' || $current_page == 'edit-notice-detail.php') ? 'active' : ''; ?>">
      <span class="menu-item-icon">📢</span>
      <span class="menu-item-text">Notice</span>
    </a>

    <a href="#pages-menu" class="menu-item <?php echo in_array($current_page, ['about-us.php', 'contact-us.php']) ? 'active' : ''; ?>" data-toggle="collapse" aria-expanded="<?php echo in_array($current_page, ['about-us.php', 'contact-us.php']) ? 'true' : 'false'; ?>">
      <span class="menu-item-icon">📄</span>
      <span class="menu-item-text">Pages</span>
    </a>
    <div class="collapse <?php echo in_array($current_page, ['about-us.php', 'contact-us.php']) ? 'show' : ''; ?>" id="pages-menu">
      <div class="sub-menu">
        <a href="about-us.php" class="menu-item sub-item <?php echo ($current_page == 'about-us.php') ? 'active' : ''; ?>">About Us</a>
        <a href="contact-us.php" class="menu-item sub-item <?php echo ($current_page == 'contact-us.php') ? 'active' : ''; ?>">Contact Us</a>
      </div>
    </div>

    <a href="search.php" class="menu-item <?php echo ($current_page == 'search.php') ? 'active' : ''; ?>">
      <span class="menu-item-icon">🔍</span>
      <span class="menu-item-text">Search</span>
    </a>
  </div>
</nav>