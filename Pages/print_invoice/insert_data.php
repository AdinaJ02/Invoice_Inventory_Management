<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $request_data = json_decode(file_get_contents('php://input'), true);

        $invoice_no = $request_data["invoice_no"];
        $memo_no = $request_data["memo_no"];
        $date = $request_data["date"];

        // Create a SQL query to insert data into the memo table
        $sql_insert_invoice = "INSERT INTO invoice (invoice_no, invoice_date, memo_no, payment_status) VALUES ('$invoice_no', '$date', '$memo_no', 'Recieved')";

        if ($conn->query($sql_insert_invoice) === TRUE) {
            echo "<p style='color:green; text-align:center;'>Data inserted successfully.</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error: " . $sql_insert_invoice . "<br>" . $conn->error . "</p>";
        }

        $conn->close();
    }
}
?>