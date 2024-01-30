<?php
include '../../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoiceNo = $_POST['invoice_no'];
    $manualEntry = $_POST['manual_entry'];
    $source = $_POST['source'];

    if ($source === 'invoice') {
        // Update the manual_entry value in the invoice table
        $updateSql = "UPDATE invoice SET manual_entry = '$manualEntry' WHERE invoice_no = '$invoiceNo'";
    } elseif ($source === 'invoice_wmemo') {
        // Update the manual_entry value in the invoice_wmemo table
        $updateSql = "UPDATE invoice_wmemo SET manual_entry = '$manualEntry' WHERE invoice_no = '$invoiceNo'";
    } else {
        // Handle the case where the source is unknown or invalid
        echo 'Invalid source';
        exit;
    }

    $updateResult = $conn->query($updateSql);

    if ($updateResult) {
        echo 'Data updated successfully';
    } else {
        echo 'Error updating data';
    }
}

$conn->close();
?>