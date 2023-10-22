<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $request_data = json_decode(file_get_contents('php://input'), true);

        $memo_no = $request_data["memo_no"];
        $date = $request_data["date"];
        $memorandum_day = $request_data["memorandum_day"];
        $name = $request_data["name"];
        $address = $request_data["address"];
        $total_wt = $request_data["total_wt"];
        $total_final_tot = $request_data["total_final_tot"];
        $data = $request_data["data"];

        // Iterate through the data and insert each row into the database
        foreach ($data as $row) {
            $lotNo = (string) $row['lot_no'];
            $desc = (string) $row['desc'];
            $shape = (string) $row['shape'];
            $size = (string) $row['size'];
            $pcs = (int) $row['pcs'];
            $wt = (float) $row['wt'];
            $color = (string) $row['color'];
            $clarity = (string) $row['clarity'];
            $certificate = (string) $row['certificate'];
            $rap = (float) $row['rap'];
            $disc = (float) $row['disc'];
            $price = (float) $row['price'];
            $total = (float) $row['total'];
            $return = (float) $row['return'];
            $kept = (float) $row['kept'];
            $final_total = (float) $row['final_total'];

            if (!empty($lotNo)) {
                $sql_check_exists = "SELECT * FROM memo_data WHERE memo_no = '$memo_no' AND lot_no = '$lotNo'";
                $check_result_exists = $conn->query($sql_check_exists);

                if ($check_result_exists->num_rows > 0) {
                    $sql_insert_memo_data = "UPDATE memo_data SET
                        `description` = '$desc',
                        shape = '$shape',
                        `size` = '$size',
                        pcs = '$pcs',
                        `weight` = '$wt',
                        color = '$color',
                        clarity = '$clarity',
                        certificate_no = '$certificate',
                        rap = '$rap',
                        discount = '$disc',
                        price = '$price',
                        total = '$total',
                        `return` = '$return',
                        kept = '$kept',
                        final_total = '$final_total'
                        WHERE memo_no = '$memo_no' AND lot_no = '$lotNo'";
                } else {
                    $sql_insert_memo_data = "INSERT INTO `memo_data`(`memo_no`, `lot_no`, `description`, `shape`, `size`, `pcs`, `weight`, `color`, `clarity`, `certificate_no`, `rap`, `discount`, `price`, `total`, `return`, `kept`, `final_total`) 
                    VALUES ('$memo_no','$lotNo','$desc','$shape','$size','$pcs','$wt','$color','$clarity','$certificate','$rap','$disc','$price','$total', '$return', '$kept', '$final_total')";
                }

                // Check if the lot number exists in stock_list
                $check_sql = "SELECT * FROM stock_list WHERE lot_no = '$lotNo'";
                $check_result = $conn->query($check_sql);

                if ($check_result->num_rows == 0) {
                    // Lot number doesn't exist, perform an insert
                    $sql_insert_stock = "INSERT INTO `stock_list`(`lot_no`, `shape`, `size`, `pcs`, `weight`, `color`, `clarity`, `certificate_no`, `rap`, `discount`, `total`, `price`) 
                    VALUES ('$lotNo','$shape','$size','$pcs','$wt','$color','$clarity','$certificate','$rap','$disc', '$total', '$price')";
                }

                if ($conn->query($sql_insert_memo_data) === TRUE || $conn->query($sql_insert_stock) === TRUE) {
                    echo 'Data Inserted successfully';
                } else {
                    echo 'Error: ' . $sql_insert_memo_data . '<br>' . $conn->error;
                }
            }
        }


        // Create a SQL query to insert data into the memo table
        $sql_update_memo = "UPDATE memo
        SET memorandum_day = '$memorandum_day', memo_date = '$date', customer_name = '$name', `address` = '$address', total_wt = '$total_wt', total_total = '$total_final_tot'
        WHERE memo_no = '$memo_no'";

        // Create a SQL query to insert data into the customer_data table
        $sql_update_customer = "UPDATE customer_data
        SET customer_name = '$name', `address` = '$address'
        WHERE memo_no = '$memo_no'";

        if ($conn->query($sql_update_memo) === TRUE && $conn->query($sql_update_customer) === TRUE) {
            echo "<p style='color:green; text-align:center;'>Data inserted successfully.</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error: " . $sql . "<br>" . $conn->error . "</p>";
        }

        // Check if the combination of customer_name and address exists
        $check_sql_customers = "SELECT * FROM customers WHERE customer_name = '$name' AND `address` = '$address'";
        $check_result = $conn->query($check_sql_customers);

        if ($check_result->num_rows == 0) {
            $sql_insert_customers = "INSERT INTO customers (customer_name, `address`) VALUES ('$name', '$address')";
            if ($conn->query($sql_insert_customers) === TRUE) {
                echo "<p style='color:green; text-align:center;'>Data inserted successfully.</p>";
            }
        }

        $conn->close();
    }
}

?>