<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $request_data = json_decode(file_get_contents('php://input'), true);

        $memo_no = $request_data["memo_no"];
        $data = $request_data["data"];

        // Check if the memo is open
        $isMemoOpen = false;
        $check_sql = "SELECT is_open FROM memo WHERE memo_no = '$memo_no'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            $isMemoOpen = ($row['is_open'] === 'open');
        }

        // If the memo is open, perform the update operation
        if ($isMemoOpen) {
            // Iterate through the data and insert each row into the database
            foreach ($data as $row) {
                $lotNo = (string) $row['lot_no'];
                $return = (float) $row['return'];

                if (!empty($lotNo)) {
                    // Check if the lot number exists in stock_list
                    $check_sql = "SELECT * FROM stock_list WHERE lot_no = '$lotNo'";
                    $check_result = $conn->query($check_sql);

                    if ($check_result->num_rows > 0) {
                        // Lot number already exists, perform an update
                        $sql_insert_stock = "UPDATE `stock_list` SET
                    `weight` = `weight` + $return
                    WHERE `lot_no` = '$lotNo'";
                    }

                    if ($conn->query($sql_insert_stock) === TRUE) {
                        echo 'Data Inserted successfully';
                    } else {
                        echo 'Error: ' . $sql_insert_memo_data . '<br>' . $conn->error;
                    }
                }
            }
        }

        // Create a SQL query to insert data into the memo table
        $sql_update_memo = "UPDATE memo
        SET is_open = 'close'
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