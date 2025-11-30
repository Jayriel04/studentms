/**
 * Toggles the visibility of a password field.
 * @param {HTMLElement} toggleButton - The button element that was clicked.
 * @param {string} inputId - The ID of the password input field to toggle.
 */
function togglePasswordVisibility(toggleButton, inputId) {
  const passwordInput = document.getElementById(inputId);
  if (!passwordInput) return;

  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    toggleButton.textContent = 'HIDE';
  } else {
    passwordInput.type = 'password';
    toggleButton.textContent = 'SHOW';
  }
}

// Reset Password Form Validation
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const newPasswordInput = document.getElementById('newpassword');
    const confirmPasswordInput = document.getElementById('confirmpassword');
    const passwordMatchErrorDiv = document.getElementById('password-match-error');
    const form = document.getElementById('resetPasswordForm');
    const passwordStrengthDiv = document.getElementById('password-strength');
    const changePasswordButton = form ? form.querySelector('button[type="submit"]') : null;

    if (!newPasswordInput || !confirmPasswordInput || !form) return;

    function validatePasswords() {
      const newPassword = newPasswordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      if (confirmPassword.length > 0 && newPassword !== confirmPassword) {
        passwordMatchErrorDiv.textContent = 'Passwords do not match.';
        if (changePasswordButton) changePasswordButton.disabled = true;
      } else {
        passwordMatchErrorDiv.textContent = '';
        if (changePasswordButton) changePasswordButton.disabled = false;
      }
    }

    newPasswordInput.addEventListener('input', function () {
      validatePasswords();
      const password = newPasswordInput.value;
      if (password.length === 0) {
        passwordStrengthDiv.innerHTML = '';
        if (changePasswordButton) changePasswordButton.disabled = false;
      } else if (password.length < 5) {
        passwordStrengthDiv.innerHTML = '<span style="color: red;">Weak: Password must be at least 5 characters.</span>';
        if (changePasswordButton) changePasswordButton.disabled = true;
      } else {
        passwordStrengthDiv.innerHTML = '<span style="color: green;">Strong</span>';
        if (changePasswordButton) changePasswordButton.disabled = newPasswordInput.value !== confirmPasswordInput.value;
      }
    });

    confirmPasswordInput.addEventListener('input', validatePasswords);

    form.addEventListener('submit', function (event) {
      if (newPasswordInput.value !== confirmPasswordInput.value) {
        event.preventDefault();
        passwordMatchErrorDiv.textContent = 'Passwords do not match. Please correct before submitting.';
      } else if (newPasswordInput.value.length < 5) {
        event.preventDefault();
        passwordMatchErrorDiv.textContent = 'Password must be at least 5 characters long.';
        if (changePasswordButton) changePasswordButton.disabled = true;
      } else {
        if (changePasswordButton) changePasswordButton.disabled = false;
      }
    });
  });
})();