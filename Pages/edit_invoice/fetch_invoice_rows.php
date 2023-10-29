<?php
include '../../connection.php';

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Retrieve memo data based on memo_no
if (isset($_GET['invoice_no'])) {
    $invoiceNo = $_GET['invoice_no'];
    $sql = "SELECT lot_no, wt, shape, color, clarity, certificate_no, rap, discount, price, total  FROM invoice_data WHERE invoice_no = $invoiceNo";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    } else {
        echo json_encode([]);
    }
}

// Close the database connection
$conn->close();
?>