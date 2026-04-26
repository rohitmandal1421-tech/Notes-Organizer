
function validateEmail(email) {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(email);
}

/**
 * Show Error Message
 */
function showError(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.style.display = 'block';
    }
}

/**
 * Clear Error Message
 */
function clearError(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = '';
        element.style.display = 'none';
    }
}

/**
 * Clear All Errors
 */
function clearAllErrors() {
    const errors = document.querySelectorAll('.form-error');
    errors.forEach(error => {
        error.textContent = '';
        error.style.display = 'none';
    });
}

console.log('✅ Validation.js loaded');
