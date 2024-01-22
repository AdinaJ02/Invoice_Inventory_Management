<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if (isset($_GET['input'])) {
    $name = $_GET['input'];
    // Implement a SQL query to fetch the customer name and address based on the provided name
    // Replace 'your_table' and 'your_name_column', 'your_address_column' with your actual table and column names
    $sql = "SELECT `customer_name`, `address` FROM customers WHERE customer_name LIKE '%$name%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = array();
        while ($row = $result->fetch_assoc()) {
            // Store both customer name and address in an associative array
            $customerData = array(
                'name' => $row['customer_name'],
                'address' => $row['address']
            );
            $data[] = $customerData;
        }
        echo json_encode($data);
    }
}
?>
