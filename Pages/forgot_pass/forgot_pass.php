<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'nfj';

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    $message = '';

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Get the user input from the form
        $email_ID = $_POST['text'];

        // Generate a random OTP ( you can customize the length as needed )
        $otp = mt_rand(1000, 9999);

        // Perform SQL query to check if the email exists
        $sql = "SELECT * FROM login WHERE email_ID = '$email_ID'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            // Email exists, so insert it into the email_verification table
            $sql_check = "SELECT * FROM forgot_pass WHERE email_ID = '$email_ID'";
            $result_check = $conn->query($sql_check);

            if ($result_check->num_rows == 1) {
                $sql_insert_email = "UPDATE forgot_pass SET otp = '$otp' WHERE email_ID = '$email_ID'";
            }else{
                $sql_insert_email = "INSERT INTO forgot_pass (email_ID, otp) VALUES ('$email_ID', '$otp')";
            }
            if ($conn->query($sql_insert_email) === TRUE) {

                $mail = new PHPMailer(true);

                try {
                    $mail->SMTPDebug = 0; // Set debugging: 0 = no debugging, 2 = verbose debugging
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
                    $mail->SMTPAuth = true;
                    $mail->Username = 'adina.jacob02@gmail.com'; // Your Gmail email address
                    $mail->Password = 'nsfr ahlx aswl qbmo'; // Your Gmail password or an App Password if you have 2-factor authentication enabled
                    $mail->SMTPSecure = 'tls'; // Use 'tls' or 'ssl'
                    $mail->Port = 587; // Adjust the port as needed

                    $mail->setFrom('adina.jacob02@gmail.com');
                    $mail->addAddress($email_ID);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP Code';
                    $mail->Body = "Your OTP code is: '$otp'"; // Replace with the actual OTP

                    $mail->send();
                    echo 'OTP sent successfully';
                } catch (Exception $e) {
                    echo 'OTP could not be sent. Error: ', $mail->ErrorInfo;
                }

                // After sending OTP, redirect to the OTP verification page
                header('Location: ../Otp_Page/Otp.php');
                exit;
            } else {
                $message = "<p style='color:red;
                '>Error inserting email into the database.</p>";
            }
        } else {
            // Email is not registered
            $message = "<p style='color:red';>Email Id is not registered</p>";
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
    <link rel="stylesheet" href="forgot_pass.css">
    <title>Login Page</title>
</head>

<body>
    <div class="login">
        <h3>Reset password</h3>
        <div class='form'>
            <form method="post" onsubmit="return validateForm()">
                <input type="text" name="text" placeholder='Email' class='text' id='username' required
                    onkeyup="validateEmail(this.value)">
                <div class="error-message" id="error-message"></div>
                <input type="submit" value="Get Otp" class='btn-login' id='do-login'></input>
            </form>
            <div id='message'>
                <?php echo $message;
                ?>
            </div>
        </div>
    </div>
    <script src='forgot_pass.js'></script>
</body>

</html>