<?php
include '../../connection.php';

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["invoice_no"])) {
        $invoiceNo = $_GET["invoice_no"];

        // Construct the SQL query to fetch data based on memo_no
        $sql = "SELECT i.`date`, i.customer_name, c.address, i.final_total, i.disc_total, i.payment_status
        FROM invoice_wmemo i 
        LEFT JOIN customers c ON i.customer_name = c.customer_name
        WHERE i.invoice_no = $invoiceNo";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Return the data as JSON
            echo json_encode($row);
        } else {
            // Return an empty JSON object if no data is found
            echo json_encode([]);
        }

        // Close the connection
        $conn->close();
    }
}
?>