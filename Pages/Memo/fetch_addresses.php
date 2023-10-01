<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if (isset($_GET['name'])) {
    $name = $_GET['name'];
    // Implement a SQL query to fetch the address based on the provided name
    // Replace 'your_table' and 'your_address_column' with your actual table and column names
    $sql = "SELECT `address` FROM customers WHERE customer_name = '$name'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $addresses = array();
        while ($row = $result->fetch_assoc()) {
            $addresses[] = $row['address'];
        }
        echo json_encode($addresses);
    }
}

?>