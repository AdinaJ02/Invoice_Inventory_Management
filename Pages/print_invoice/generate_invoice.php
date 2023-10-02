<?php
include '../../connection.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the last memo number from the database
$sql = "SELECT invoice_no FROM invoice ORDER BY invoice_no DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastMemoNo = $row["invoice_no"];
} else {
    // If no memo number exists in the database, start from 5000
    $lastMemoNo = 0;
}

// Calculate the next memo number
$nextMemoNo = $lastMemoNo + 1;

// Close the database connection
$conn->close();

// Return the next memo number as a JSON response
echo json_encode(array("next_memo_no" => $nextMemoNo));
?>