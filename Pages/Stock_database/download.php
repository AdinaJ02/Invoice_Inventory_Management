<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="stock_list.csv"');

require '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Query to retrieve data from the database
$sql = "SELECT * FROM stock_list"; // Replace 'your_table_name' with the actual table name
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output CSV column headers
    echo "Lot No,Description,Shape,Size,Pcs,Wt (cts),Color,Clarity,Certificate,Video,Cut,POL,SYM,FL,M1,M2,M3,TAB,DEP,Rap (\$),Dis,Total,Price,Name,Average weight\n";

    // Output each row as a CSV line
    while ($row = $result->fetch_assoc()) {
        echo implode(',', $row) . "\n";
    }
}
?>
