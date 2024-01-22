<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = urldecode($_GET['email']);
    $new_password = $_POST['new_password'];
    // echo $email;
    // echo $new_password;
    // Update the password in the login database
    $update_sql = "UPDATE `login` SET `password` = '$new_password' WHERE `email_ID` = $email";

    if ($conn->query($update_sql) === TRUE) {
        // Password updated successfully
        $message = "<p style='color:white';>Password updated successfully</p>";
        header('Location: ../../index.php');
    } else {
        // Error updating password
        echo 'Error updating password: ' . $conn->error;
    }
}
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="new_pass.css">
    <title>Create new password Page</title>
</head>

<body>
    <div class="login">
        <div class='form'>
            <form method="post" onsubmit="return validateForm()">
                <input type="password" name="new_password" placeholder='Create New Password' class='password'
                    id='new-password' required onkeyup="validateNewPassword(this.value)"><br>
                <input type="password" placeholder='Confirm Password' class='password' id='confirm-password' required
                    onkeyup="validateConfirmPassword(this.value)"><br>
                <input type="submit" value="Create Password" class='btn-login' id='do-login'></input>
            </form>
            <div id='message'>
                <?php echo $message;
                ?>
            </div>
            <div class="error-message" id="error-message"></div>
        </div>
    </div>
    <script src="new_pass.js"></script>
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