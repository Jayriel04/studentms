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

    <a href="view-notice.php" class="menu-item <?php echo ($current_page == 'view-notice.php') ? 'active' : ''; ?>">
      <span class="menu-item-icon">🔔</span>
      <span class="menu-item-text">View Notice</span>
    </a>

    <a href="student-profile.php" class="menu-item <?php echo ($current_page == 'student-profile.php') ? 'active' : ''; ?>">
      <span class="menu-item-icon">👤</span>
      <span class="menu-item-text">View Profile</span>
    </a>
  </div>
</nav>