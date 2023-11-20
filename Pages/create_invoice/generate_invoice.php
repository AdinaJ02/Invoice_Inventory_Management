<?php
include '../../connection.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT invoice_no FROM invoice_nos ORDER BY invoice_no DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastInvoiceNo = $row["invoice_no"];
} else {
    $lastInvoiceNo = 0;
}

$nextInvoiceNo = $lastInvoiceNo + 1;


// Close the database connection
$conn->close();

echo json_encode(array("next_invoice_no" => $nextInvoiceNo));
?>