function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.querySelector('.toggle-password');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.textContent = 'HIDE';
    } else {
        passwordInput.type = 'password';
        toggleBtn.textContent = 'SHOW';
    }
}

// Password strength validation for signup form
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const passwordStrengthDiv = document.getElementById('password-strength');
    const signupButton = document.querySelector('button[name="signup"]');

    if (!passwordInput || !passwordStrengthDiv || !signupButton) return;

    passwordInput.addEventListener('input', function () {
      const password = passwordInput.value;
      if (password.length === 0) {
        passwordStrengthDiv.innerHTML = '';
        signupButton.disabled = false;
      } else if (password.length < 5) {
        passwordStrengthDiv.innerHTML = '<span style="color: red;">Weak: Password must be at least 5 characters.</span>';
        signupButton.disabled = true;
      } else {
        passwordStrengthDiv.innerHTML = '<span style="color: green;">Strong</span>';
        signupButton.disabled = false;
      }
    });
  });
})();