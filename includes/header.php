<!-- Enhanced Modern Header -->
<header class="modern-header" role="banner">
  <div class="modern-header-container">
    <a class="modern-logo" href="index.php" title="Student Profiling System">
      <span class="modern-logo-text"><span class="modern-logo-accent">Student</span>Vue</span>
    </a>
    <nav class="modern-nav" role="navigation" aria-label="Main Navigation">
      <a class="modern-nav-link scroll" href="index.php#top">Home</a>
      <a class="modern-nav-link scroll" href="index.php#about">About</a>
      <a class="modern-nav-link scroll" href="index.php#notice">Notice</a>
      <a class="modern-nav-link scroll" href="index.php#contact">Contact</a>
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

<!-- Global toast container and helper (available to all pages that include header.php) -->
<style>
  /* Minimal toast styles to avoid dependency issues */
  #appToastContainer {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 1080;
  }

  .app-toast {
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    padding: 12px 16px;
    margin-bottom: 8px;
    min-width: 220px;
    display: flex;
    gap: 8px;
    align-items: center;
  }

  .app-toast.info {
    border-left: 4px solid #17a2b8;
  }

  .app-toast.success {
    border-left: 4px solid #28a745;
  }

  .app-toast.warning {
    border-left: 4px solid #ffc107;
  }

  .app-toast.danger {
    border-left: 4px solid #dc3545;
  }

  .app-toast .app-toast-close {
    margin-left: auto;
    cursor: pointer;
    color: #666;
  }
</style>

<div id="appToastContainer" aria-live="polite" aria-atomic="true"></div>
<script>
  window.showToast = function (message, type) {
    type = type || 'info';
    try {
      var container = document.getElementById('appToastContainer');
      if (!container) return;
      var toast = document.createElement('div');
      toast.className = 'app-toast ' + (['info', 'success', 'warning', 'danger'].indexOf(type) === -1 ? 'info' : type);
      toast.innerHTML = '<div class="app-toast-body">' + (message || '') + '</div>' +
        '<div class="app-toast-close" role="button" aria-label="close">&times;</div>';
      container.appendChild(toast);
      // close handler
      toast.querySelector('.app-toast-close').addEventListener('click', function () { container.removeChild(toast); });
      // auto remove
      setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 3500);
    } catch (e) {
      console && console.error && console.error(e);
    }
  };
</script>