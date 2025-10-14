// Function to toggle the "Please Specify" input field for gender
function toggleOtherGenderInput() {
  const genderSelect = document.getElementById("gender");
  const otherGenderInput = document.getElementById("otherGenderInput");

  if (genderSelect.value === "Other") {
    otherGenderInput.style.display = "block";
  } else {
    otherGenderInput.style.display = "none";
    document.getElementById("otherGender").value = ""; // Clear the input field
  }
}

// Function to display toast messages
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
    document.body.appendChild(container);
  }
  container.appendChild(toast);

  // Auto remove after 3s, using jQuery's toast hide if available
  setTimeout(function () { try { $(toast).toast('hide'); } catch (e) { toast.remove(); } }, 3000);
}

/**
 * Sets up a click listener for a password toggle icon.
 * @param {string} toggleId The ID of the toggle icon element.
 * @param {string} passwordId The ID of the password input element.
 */
function setupPasswordToggle(toggleId, passwordId) {
  const toggle = document.querySelector(toggleId);
  const passwordInput = document.querySelector(passwordId);

  if (toggle && passwordInput) {
    toggle.addEventListener('click', function () {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
    });
  }
}

document.addEventListener('DOMContentLoaded', function () {
  // Setup for various admin pages
  setupPasswordToggle('#togglePassword', '#password'); // For login.php, add-staff.php, add-students.php, edit-student-detail.php
  setupPasswordToggle('#toggleCurrentPassword', '#currentpassword'); // For change-password.php
  setupPasswordToggle('#toggleNewPassword', '#newpassword'); // For change-password.php and reset-password-process.php
  setupPasswordToggle('#toggleConfirmPassword', '#confirmpassword'); // For change-password.php and reset-password-process.php

  // Delegate close buttons for toasts
  document.addEventListener('click', function (e) {
    if (e.target && e.target.matches('[data-dismiss="toast"]')) {
      var t = e.target.closest('.toast');
      if (t) {
        try {
          $(t).toast('hide');
        } catch (err) {
          t.remove();
        }
      }
    }
  });

  // Set initial state for the 'Other' gender input on page load
  if (document.getElementById('gender')) {
    toggleOtherGenderInput();
  }
});