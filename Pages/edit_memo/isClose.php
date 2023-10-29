<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $request_data = json_decode(file_get_contents('php://input'), true);

        // Assuming you have the 'memo_no' value in your request data
        $memo_no = $request_data["memo_no"];

        // Query to fetch the 'is_open' value from the 'memo' table
        $sql_get_is_open = "SELECT is_open FROM memo WHERE memo_no = '$memo_no'";
        $result = $conn->query($sql_get_is_open);

        if ($result->num_rows > 0) {
            // Assuming there is only one row for a specific 'memo_no'
            $row = $result->fetch_assoc();
            $is_open = $row['is_open'];

            // Return the value as a JSON response
            echo json_encode(['status' => $is_open]);

        } else {
            // Return a JSON response indicating "Memo not found"
            echo json_encode(['status' => 'Memo not found']);
        }
    }
}

// Close the database connection when done
$conn->close();
?>
