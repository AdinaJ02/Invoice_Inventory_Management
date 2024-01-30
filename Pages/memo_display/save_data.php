<?php
include '../../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memoNo = $_POST['memo_no'];
    $manualEntry = $_POST['manual_entry'];
    $status = $_POST['status'];

    // Update the database with the new values
    $updateSql = "UPDATE memo SET manual_entry = '$manualEntry', status = '$status' WHERE memo_no = '$memoNo'";
    $updateResult = $conn->query($updateSql);

    if ($updateResult) {
        echo 'Data updated successfully';
    } else {
        echo 'Error updating data';
    }
}

$conn->close();

?>