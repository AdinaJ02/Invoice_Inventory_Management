<?php
include '../../connection.php';

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["memo_no"])) {
        $memoNo = $_GET["memo_no"];

        // Construct the SQL query to fetch data based on memo_no
        $sql = "SELECT memorandum_day, memo_date, customer_name, `address` FROM memo WHERE memo_no = $memoNo";

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