<?php
include '../../connection.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$memo_no = isset($_GET['memo_no']) ? $_GET['memo_no'] : null;

// Check if memo_no exists in the invoice table
$check_sql = "SELECT invoice_no FROM invoice WHERE memo_no = '$memo_no'";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows > 0) {
    // Memo_no exists in the invoice table, fetch the corresponding invoice_no
    $row = $check_result->fetch_assoc();
    $nextInvoiceNo = $row["invoice_no"];
} else {
    // Memo_no doesn't exist in the invoice table, generate the next invoice number
    $sql = "SELECT invoice_no FROM invoice_nos ORDER BY invoice_no DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastInvoiceNo = $row["invoice_no"];
    } else {
        $lastInvoiceNo = 0;
    }

    $nextInvoiceNo = $lastInvoiceNo + 1;
}

// Close the database connection
$conn->close();

echo json_encode(array("next_invoice_no" => $nextInvoiceNo));
?>