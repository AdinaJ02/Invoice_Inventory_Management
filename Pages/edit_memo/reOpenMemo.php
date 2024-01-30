<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $request_data = json_decode(file_get_contents('php://input'), true);

        $memo_no = $request_data["memo_no"];

        // Create a SQL query to insert data into the memo table
        $sql_update_memo = "UPDATE memo
        SET is_open = 'open'
        WHERE memo_no = '$memo_no'";

        if ($conn->query($sql_update_memo) === TRUE) {
            echo "<p style='color:green; text-align:center;'>Data updated successfully.</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error: " . $sql . "<br>" . $conn->error . "</p>";
        }

        $conn->close();
    }
}

?>