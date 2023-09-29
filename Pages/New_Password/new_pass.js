function validateNewPassword(newPassword) {
    const minLength = 8;
    const errorElement = document.getElementById('error-message');
    if (newPassword.length < minLength) {
        errorElement.textContent = 'Password should be at least 8 characters';
    } else {
        errorElement.textContent = '';
    }
}

function validateConfirmPassword(confirmPassword) {
    const newPassword = document.getElementById('new-password').value;
    const errorElement = document.getElementById('error-message');
    if (newPassword !== confirmPassword) {
        errorElement.textContent = 'Passwords do not match';
    } else {
        errorElement.textContent = '';
    }
}

function validateForm() {
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const minLength = 8;
    const errorElement = document.getElementById('error-message');

    if (newPassword.length < minLength) {
        errorElement.textContent = 'Password should be at least 8 characters';
        return false;
    } else if (newPassword !== confirmPassword) {
        errorElement.textContent = 'Passwords do not match';
        return false;
    }

    // If both passwords are valid and match, the form will be submitted.
    return true;
}