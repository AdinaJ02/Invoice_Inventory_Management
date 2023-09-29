function validateEmail(email) {
    const emailRegex = /^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/;
    const errorElement = document.getElementById('error-message');
    if (!emailRegex.test(email)) {
        errorElement.textContent = 'Enter a valid email address';
    } else {
        errorElement.textContent = '';
    }
}

function validateForm() {
    const email = document.getElementById('username').value;
    const emailRegex = /^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/;
    const errorElement = document.getElementById('error-message');

    if (!emailRegex.test(email)) {
        errorElement.textContent = 'Enter a valid email address';
        return false;
    }

    // If email is valid, the form will be submitted.
    return true;
}