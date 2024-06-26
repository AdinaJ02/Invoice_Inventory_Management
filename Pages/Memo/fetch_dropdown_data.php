<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve data from the database based on the provided value (e.g., shape or size)
$value = $_GET['value']; // Assuming 'value' is the parameter sent from JavaScript

// Create a SQL query to fetch data based on partial matches using the LIKE operator
$sql = "SELECT * FROM stock_list WHERE description LIKE '%$value%' OR shape LIKE '%$value%' OR size LIKE '%$value%'"; 

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Return the data as JSON
    echo json_encode($data);
} else {
    // Return an empty JSON array if no data is found
    echo json_encode([]);
}

// Close the database connection
$conn->close();
?>
