function validateEmail(email) {
    const emailRegex = /^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/;
    const errorElement = document.getElementById('error-message');
    if (!emailRegex.test(email)) {
        errorElement.textContent = 'Enter a valid email address';
    } else {
        errorElement.textContent = '';
    }
}

function validatePassword(password) {
    const minLength = 8;
    const maxLength = 10;
    const errorElement = document.getElementById('error-message');
    if (password.length < minLength) {
        errorElement.textContent = 'Password should be at least 8 characters';
    } else if (password.length > maxLength) {
        errorElement.textContent = 'Password should be less than 10 characters';
    } else {
        errorElement.textContent = '';
    }
}

function validateForm() {
    const email = document.getElementById('username').value;
    const password = document.querySelector('.password').value;

    const emailRegex = /^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/;
    const minLength = 8;
    const maxLength = 10;
    const errorElement = document.getElementById('error-message');

    if (!emailRegex.test(email)) {
        errorElement.textContent = 'Enter a valid email address';
        return false;
    } else if (password.length < minLength) {
        errorElement.textContent = 'Password should be at least 8 characters';
        return false;
    } else if (password.length > maxLength) {
        errorElement.textContent = 'Password should be less than 10 characters';
        return false;
    }

    // If both email and password are valid, the form will be submitted.
    return true;
}