document.addEventListener("DOMContentLoaded", function () {
  /**
   * Sets up a click listener for a password toggle icon.
   * @param {string} toggleId The ID of the toggle icon element.
   * @param {string} passwordId The ID of the password input element.
   */
  function setupPasswordToggle(toggleId, passwordId) {
    const toggle = document.querySelector(toggleId);
    const passwordInput = document.querySelector(passwordId);

    if (toggle && passwordInput) {
      toggle.addEventListener("click", function () {
        // Toggle the type attribute
        const type =
          passwordInput.getAttribute("type") === "password"
            ? "text"
            : "password";
        passwordInput.setAttribute("type", type);
      });
    }
  }

  // Setup for various staff pages
  setupPasswordToggle("#togglePassword", "#password"); // For login.php
  setupPasswordToggle("#toggleCurrentPassword", "#currentpassword"); // For change-password.php
  setupPasswordToggle("#toggleNewPassword", "#newpassword"); // For change-password.php and reset-password-process.php
  setupPasswordToggle("#toggleConfirmPassword", "#confirmpassword"); // For change-password.php and reset-password-process.php

  // Initialize Bootstrap toasts for login errors
  var toastEl = document.getElementById("errorToast");
  if (toastEl) {
    if (window.$) {
      $(toastEl).toast("show");
    } else if (typeof bootstrap !== "undefined") {
      var toast = new bootstrap.Toast(toastEl);
      toast.show();
    }
  }
});

// Minimal showToast fallback for pages that don't include header.php
function showToast(message, type) {
  type = type || 'info';
  var toast = document.createElement('div');
  toast.className = 'toast show';
  toast.setAttribute('role', 'alert');
  var headerClass = 'bg-info';
  if (type === 'success') headerClass = 'bg-success';
  if (type === 'error' || type === 'danger') headerClass = 'bg-danger';
  if (type === 'warning') headerClass = 'bg-warning';

  toast.innerHTML = '<div class="toast-header ' + headerClass + ' text-white"><strong class="mr-auto">' + (type.charAt(0).toUpperCase() + type.slice(1)) + '</strong><button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button></div><div class="toast-body">' + message + '</div>';

  var container = document.getElementById('appToast');
  if (!container) {
    container = document.createElement('div');
    container.id = 'appToast';
    // Add styles for positioning
    container.style.position = 'fixed';
    container.style.top = '1rem';
    container.style.right = '1rem';
    container.style.zIndex = '2000';
    document.body.appendChild(container);
  }
  container.appendChild(toast);

  // Auto remove after 3s, using jQuery's toast hide if available
  setTimeout(function () { try { $(toast).toast('hide'); } catch (e) { toast.remove(); } }, 3000);
}

function valid() {
  var n = document.getElementById("newpassword");
  var c = document.getElementById("confirmpassword");
  if (n.value.length < 6) {
    if (window.showToast)
      showToast("Password must be at least 6 characters.", "warning");
    else alert("Password must be at least 6 characters.");
    n.focus();
    return false;
  }
  if (n.value !== c.value) {
    if (window.showToast)
      showToast("New Password and Confirm Password do not match.", "warning");
    else alert("Passwords do not match!");
    c.focus();
    return false;
  }
  return true;
}

function checkpass(){
  if(document.changepassword.newpassword.value!=document.changepassword.confirmpassword.value)
  {
  alert('New Password and Confirm Password field does not match');
  document.changepassword.confirmpassword.focus();
  return false;
  }
  return true;
}
