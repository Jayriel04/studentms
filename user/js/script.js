/**
 * Toggles the visibility of an input field when "Other" is selected in a dropdown.
 * @param {string} selectId The ID of the select element.
 * @param {string} otherInputId The ID of the element to show/hide.
 */
function toggleOtherInput(selectId, otherInputId) {
  const select = document.getElementById(selectId);
  const otherInputGroup = document.getElementById(otherInputId);

  function toggle() {
    if (select && otherInputGroup) {
      // Show or hide the input field based on the selected value
      if (select.value === "Other") {
        otherInputGroup.style.display = "block"; // Show input
      } else {
        otherInputGroup.style.display = "none"; // Hide input
      }
    }
  }

  if (select) {
    select.addEventListener('change', toggle);
    toggle(); // Initial check on page load
  }
}

document.addEventListener('DOMContentLoaded', function () {
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

  // Setup for various pages
  setupPasswordToggle('#togglePassword', '#password'); // For login.php, signup.php, and update-profile.php
  setupPasswordToggle('#toggleNewPassword', '#newpassword'); // For reset-password-process.php
  setupPasswordToggle('#toggleConfirmPassword', '#confirmpassword'); // For reset-password-process.php
  setupPasswordToggle('#toggleConfirmPassword', '#confirmpassword'); // For reset-password-process.php and change-password.php
  setupPasswordToggle('#toggleCurrentPassword', '#currentpassword'); // For change-password.php
});

document.addEventListener('DOMContentLoaded', function () {
  // For update-profile.php gender field
  toggleOtherInput('gender', 'otherGenderInput');

  // For add-student.php gender field
  toggleOtherInput('genderSelect', 'otherInputGroup');
});

/**
 * Sets the active class on a clicked sidebar menu item.
 * @param {HTMLElement} element The clicked menu item element.
 */
function selectMenu(element) {
  const items = document.querySelectorAll('.menu-item');
  items.forEach(item => item.classList.remove('active'));
  element.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function () {
  // Add click listeners to new menu items
  const menuItems = document.querySelectorAll('.sidebar .menu-item');
  menuItems.forEach(item => {
    item.addEventListener('click', function() {
      selectMenu(this);
    });
  });
});
