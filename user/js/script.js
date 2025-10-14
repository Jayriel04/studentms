function toggleOtherInput() {
  const select = document.getElementById("genderSelect");
  const otherInputGroup = document.getElementById("otherInputGroup");

  // Show or hide the input field based on the selected value
  if (select.value === "Other") {
    otherInputGroup.style.display = "block"; // Show input
  } else {
    otherInputGroup.style.display = "none"; // Hide input
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
  setupPasswordToggle('#togglePassword', '#password'); // For login.php and signup.php
  setupPasswordToggle('#toggleNewPassword', '#newpassword'); // For reset-password-process.php
  setupPasswordToggle('#toggleConfirmPassword', '#confirmpassword'); // For reset-password-process.php
  setupPasswordToggle('#toggleConfirmPassword', '#confirmpassword'); // For reset-password-process.php and change-password.php
  setupPasswordToggle('#toggleCurrentPassword', '#currentpassword'); // For change-password.php
});
