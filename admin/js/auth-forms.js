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