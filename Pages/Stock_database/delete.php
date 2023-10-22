<?php
require '../../vendor/autoload.php';
include '../../connection.php';

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the lotNo from the request
    $requestData = json_decode(file_get_contents('php://input'), true);
    $lotNo = $requestData['lotNo'];

    // Perform the deletion in the database based on the "Lot No"
    $sql = "DELETE FROM stock_list WHERE lot_no = '$lotNo'"; // Replace 'stock_list' with your actual table name and 'lot_no' with the appropriate column

    if ($conn->query($sql) === TRUE) {
        echo 'Row deleted successfully';
    } else {
        echo 'Error deleting the row: ' . $conn->error;
    }
}
?>