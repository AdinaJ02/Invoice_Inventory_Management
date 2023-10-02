<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if (isset($_GET['lotNo'])) {
    $lotNo = $_GET['lotNo'];

    // Implement a SQL query to fetch data based on the provided lot_no
    $sql = "SELECT `weight`, `size`, `pcs`, `shape`, `color`, `clarity`, `certificate_no`, `rap`, `discount`
            FROM `stock_list`
            WHERE `lot_no` = '$lotNo'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    }
}

$conn->close();
?>
