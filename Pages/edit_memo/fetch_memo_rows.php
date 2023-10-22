<?php
include '../../connection.php';

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Retrieve memo data based on memo_no
if (isset($_GET['memo_no'])) {
    $memoNo = $_GET['memo_no'];

    // Check the value of is_open from the memo table
    $sqlCheckOpen = "SELECT is_open FROM memo WHERE memo_no = $memoNo";
    $resultCheckOpen = $conn->query($sqlCheckOpen);

    if ($resultCheckOpen->num_rows > 0) {
        $row = $resultCheckOpen->fetch_assoc();
        $is_open = $row['is_open'];

        if ($is_open === 'open') {
            // Perform queries for open memos
            $sql = "SELECT memo_data.lot_no, memo_data.description, memo_data.shape, memo_data.size, memo_data.pcs, stock_list.weight, memo_data.color, memo_data.clarity, memo_data.certificate_no, memo_data.rap, memo_data.discount, memo_data.price, memo_data.total, memo_data.return, memo_data.kept, memo_data.final_total 
                    FROM memo_data
                    JOIN stock_list ON memo_data.lot_no = stock_list.lot_no
                    WHERE memo_no = $memoNo";
        } else {
            // Perform queries for closed memos
            $sql = "SELECT lot_no, `description`, shape, `size`, pcs, `weight`, color, clarity, certificate_no, rap, discount, price, total, `return`, kept, final_total  FROM memo_data WHERE memo_no = $memoNo";
        }

        // Execute the appropriate SQL query
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
}

// Close the database connection
$conn->close();
?>