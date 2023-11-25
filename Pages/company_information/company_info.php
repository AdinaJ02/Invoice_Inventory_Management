<?php
include '../../connection.php';

session_start();
// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: ../../index.php');
  exit;
} else {
    include 'company_info.html';
}

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Define the columns to retrieve
$columns = 'company_name, `desc`, phone_no, address, email, disclaimer_memo, terms_invoice, `currency`, bank_details';

// Construct the SQL query
$sql = "SELECT $columns FROM `company_info`";
$result = $conn->query($sql);

// Check if the query was successful
if ($result === false) {
    die('Query failed: ' . $conn->error);
} else {
    $data = $result->fetch_assoc();
    $jsonData = json_encode($data);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('JSON encoding error: ' . json_last_error_msg());
    }
    echo $jsonData;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $companyName = mysqli_real_escape_string($conn, $_POST["companyName"]);
    $description = mysqli_real_escape_string($conn, $_POST["description"]);
    $phone = mysqli_real_escape_string($conn, $_POST["phone"]);
    $address = mysqli_real_escape_string($conn, $_POST["address"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $disclaimer = mysqli_real_escape_string($conn, $_POST["disclaimer"]);
    $terms = mysqli_real_escape_string($conn, $_POST["terms"]);
    $currency = mysqli_real_escape_string($conn, $_POST["currency"]);
    $bank_details = mysqli_real_escape_string($conn, $_POST["bank_details"]);

    // Handle logo file upload
    if ($_FILES["logo"]["error"] == UPLOAD_ERR_OK) {
        if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
            $newImage = file_get_contents($_FILES["logo"]["tmp_name"]);

            // Update the existing image in the database
            $query = "UPDATE `company_info` SET logo = ? WHERE id = 1";
            $stmt = $conn->prepare($query);

            if ($stmt) {
                $stmt->bind_param('s', $newImage);

                if ($stmt->execute()) {
                    echo "<p style='color: green;'>Image updated successfully!</p>";
                } else {
                    echo "<p style='color: red;'>Image update failed: " . $stmt->error . "</p>";
                    ;
                }

                $stmt->close();
            } else {
                echo "<p style='color: red;'>Error preparing statement: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>No file uploaded or an error occurred while uploading the image.</p>";
        }
    } else {
        echo "<p style='color: red;'>No file uploaded.</p>";
    }

    // Update the company information in the database
    $queryupdate = "UPDATE `company_info` SET 
              company_name = '$companyName', 
              `desc` = '$description', 
              phone_no = '$phone', 
              `address` = '$address', 
              email = '$email', 
              disclaimer_memo = '$disclaimer', 
              terms_invoice = '$terms',
              currency = '$currency',
              bank_details = '$bank_details'
              WHERE id = 1";

    if ($conn->query($queryupdate)) {
        echo "<p style='color: green;'>Company information updated successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error updating company information: " . mysqli_error($conn) . "</p>";
    }
}

// Close the connection
$conn->close();
?>