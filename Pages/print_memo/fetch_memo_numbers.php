<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Query to retrieve memo numbers
$sql = "SELECT memo_no FROM memo"; // Replace 'your_table_name' with your actual table name
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $memoNumbers = array();
    while ($row = $result->fetch_assoc()) {
        $memoNumbers[] = $row['memo_no'];
    }
    echo json_encode($memoNumbers);
} else {
    echo json_encode(array()); // Return an empty array if no memo numbers are found
}

// Close the connection
$conn->close();
?>
