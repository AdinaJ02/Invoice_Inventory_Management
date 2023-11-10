<?php
include '../../connection.php';

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Retrieve memo data based on invoice_no
if (isset($_GET['invoice_no'])) {
    $invoiceNo = $_GET['invoice_no'];

    // Construct the SQL query to fetch data based on invoice_no from both tables
    $sql = "SELECT md.lot_no, md.kept, md.shape, md.color, md.clarity, md.certificate_no, md.rap, md.discount, md.price, md.final_total  
            FROM invoice i
            JOIN memo_data md ON i.memo_no = md.memo_no
            WHERE i.invoice_no = $invoiceNo";

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
