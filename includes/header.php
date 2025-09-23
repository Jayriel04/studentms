<!-- Enhanced Modern Header -->
<header class="modern-header" role="banner">
  <div class="modern-header-container">
    <a class="modern-logo" href="index.php" title="Student Profiling System">
      <span class="modern-logo-text"><span class="modern-logo-accent">Student</span>Vue</span>
    </a>
    <nav class="modern-nav" role="navigation" aria-label="Main Navigation">
      <a class="modern-nav-link" href="index.php">Home</a>
      <a class="modern-nav-link" href="about.php">About</a>
      <a class="modern-nav-link" href="contact.php">Contact</a>
      <a class="modern-nav-link modern-btn-nav" href="admin/login.php">Admin</a>
      <a class="modern-nav-link modern-btn-nav" href="staff/login.php">Staff</a>
      <!-- <a class="modern-nav-link modern-btn-nav" href="user/login.php">Student</a> -->
    </nav>
    <button class="modern-nav-toggle" aria-label="Open Menu" aria-expanded="false">
      <span class="modern-nav-toggle-bar"></span>
      <span class="modern-nav-toggle-bar"></span>
      <span class="modern-nav-toggle-bar"></span>
    </button>
  </div>
</header>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var navToggle = document.querySelector('.modern-nav-toggle');
    var nav = document.querySelector('.modern-nav');
    if (navToggle && nav) {
      navToggle.addEventListener('click', function () {
        var expanded = navToggle.getAttribute('aria-expanded') === 'true';
        nav.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', !expanded);
      });
    }
  });
</script>