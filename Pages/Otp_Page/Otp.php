<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
  die('Connection failed: ' . $conn->connect_error);
} else {
  $message = '';

  // Check if the form is submitted
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the submitted OTP values
    $otp1 = $_POST['otp1'];
    $otp2 = $_POST['otp2'];
    $otp3 = $_POST['otp3'];
    $otp4 = $_POST['otp4'];

    // Combine the OTP digits
    $enteredOTP = $otp1 . $otp2 . $otp3 . $otp4;

    // Check the entered OTP against the stored OTP in the database
    $sql = "SELECT * FROM forgot_pass WHERE otp = '$enteredOTP'";
    $result = $conn->query($sql);

    
    // Assuming that the query should return a single row with the email ID
    if ($result->num_rows > 0) {
      // Assuming that the query should return a single row with the email ID
      $row = $result->fetch_assoc();
      $email = $row['email_ID'];
      $encodedEmail = urlencode($email);
      // Now, the $email variable contains the retrieved email ID as a string
      echo "Email ID: " . $email;
  }
    

    if ($result->num_rows == 1) {
      // OTP matched, redirect to new_pass.html
      header("Location: ../New_Password/new_pass.php?email='$encodedEmail'");
      

      // Delete the record from the database
      $sqlDelete = "DELETE FROM forgot_pass WHERE otp = '$enteredOTP'";
      if ($conn->query($sqlDelete) === TRUE) {
        // Record deleted successfully
      } else {
        // Error deleting record
        echo 'Error: ' . $conn->error;
      }
    } else {
      // OTP incorrect, display an error message
      $message = "<p style='color:red';>OTP is not correct</p>";
    }

    $conn->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="Otp.css" />
  <title>Login Page</title>
</head>

<body>
  <div class=" login">
    <h3>Enter Otp</h3>
    <div id="form" class="form">
      <form id="otpForm" method="post">
        <input id="otp1" class="input" type="text" name="otp1" inputmode="numeric" maxlength="1" required />
        <input id="otp2" class="input" type="text" name="otp2" inputmode="numeric" maxlength="1" required />
        <input id="otp3" class="input" type="text" name="otp3" inputmode="numeric" maxlength="1" required />
        <input id="otp4" class="input" type="text" name="otp4" inputmode="numeric" maxlength="1" required />
    </div>
    <input type="submit" value="Submit Otp" class='btn-login' id='do-login'></input>
    <div id='message'>
      <?php echo $message;
      ?>
    </div>
    </form>
  </div>
  <script src="Otp.js"></script>
</body>

</html>