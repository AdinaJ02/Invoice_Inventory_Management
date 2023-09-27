<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'nfj';

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Define the columns to retrieve
$columns = 'company_name, `desc`, phone_no, address, email, disclaimer_memo, terms_invoice';

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

// Close the connection
$conn->close();
?>