<?php
include '../../connection.php';

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Retrieve memo data based on memo_no
if (isset($_GET['memo_no'])) {
    $memoNo = $_GET['memo_no'];
    $sql = "SELECT lot_no, `description`, shape, `size`, pcs, `weight`, color, clarity, certificate_no, rap, discount, price, total, `return`, kept, final_total  FROM memo_data WHERE memo_no = $memoNo";
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