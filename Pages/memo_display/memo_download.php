<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="Memo_reports.csv"');

require '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Query to retrieve data from the database
$sql = "SELECT memo_no, memo_date, customer_name, total_wt, total_total, is_open, manual_entry, `status` FROM memo"; // Replace 'your_table_name' with the actual table name
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output CSV column headers
    echo "Memo no., Date, Customer Name, Total Weight, Final Total, Is Open, Manual Entry, Status\n";

    // Output each row as a CSV line
    while ($row = $result->fetch_assoc()) {
        echo implode(',', $row) . "\n";
    }
}
?>
