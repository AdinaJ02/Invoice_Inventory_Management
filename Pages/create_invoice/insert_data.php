<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $request_data = json_decode(file_get_contents('php://input'), true);

        $invoice_no = $request_data["invoice_no"];
        $date = $request_data["date"];
        $name = $request_data["name"];
        $address = $request_data["address"];
        $total_wt = $request_data["total_wt"];
        $total_final_tot = $request_data["total_final_tot"];
        $disc_total = $request_data["disc_total"];
        $paymentStatus = $request_data["paymentStatus"];
        $data = $request_data["data"];

        $fetch_paymentStatus_sql = "SELECT payment_status FROM invoice_wmemo WHERE invoice_no = '$invoice_no'";
        $paymentStatusResult = $conn->query($fetch_paymentStatus_sql);

        if ($paymentStatusResult->num_rows > 0) {
            $row = $paymentStatusResult->fetch_assoc();
            $paymentStatusInDatabase = $row['payment_status'];
        } else {
            $paymentStatusInDatabase = ''; // Set a default value if no record is found
        }

        $sql_insert_invoice = "INSERT INTO invoice_wmemo (invoice_no, `date`, customer_name, total_wt, final_total, disc_total, payment_status) VALUES ('$invoice_no', '$date', '$name', '$total_wt', '$total_final_tot', $disc_total, '$paymentStatus')";

        // Check if the combination of customer_name and address exists
        $check_sql_customers = "SELECT * FROM customers WHERE customer_name = '$name' AND `address` = '$address'";
        $check_result = $conn->query($check_sql_customers);

        if ($check_result->num_rows == 0) {
            $sql_insert_customers = "INSERT INTO customers (customer_name, `address`) VALUES ('$name', '$address')";
            if ($conn->query($sql_insert_customers) === TRUE) {
                echo "<p style='color:green; text-align:center;'>Data inserted successfully.</p>";
            }
        }

        if ($conn->query($sql_insert_invoice) === TRUE) {
            echo "<p style='color:green; text-align:center;'>Data inserted successfully.</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error: " . $sql . "<br>" . $conn->error . "</p>";
        }

        // Iterate through the data and insert each row into the database
        foreach ($data as $row) {
            $lotNo = (string) $row['lot_no'];
            $desc = (string) $row['desc'];
            $wt = (float) $row['wt'];
            $shape = (string) $row['shape'];
            $color = (string) $row['color'];
            $clarity = (string) $row['clarity'];
            $certificate = (string) $row['certificate'];
            $rap = (float) $row['rap'];
            $disc = (float) $row['disc'];
            $price = (float) $row['price'];
            $total = (float) $row['final_total'];

            if (!empty($lotNo)) {
                $sql_insert_data = "INSERT INTO `invoice_data`(`invoice_no`, `lot_no`, `description`, `wt`, `shape`, `color`, `clarity`, `certificate_no`, `rap`, `discount`, `price`, `total`) 
                        VALUES ('$invoice_no','$lotNo','$desc','$wt','$shape','$color','$clarity','$certificate','$rap','$disc','$price','$total')";

                // Check if the lot number exists in stock_list
                $check_sql = "SELECT * FROM stock_list WHERE lot_no = '$lotNo'";
                $check_result = $conn->query($check_sql);

                if ($check_result->num_rows === 0) {
                    // Lot number doesn't exist, perform an insert
                    $sql_insert_stock = "INSERT INTO `stock_list`(`lot_no`, `shape`, `description`, `weight`, `color`, `clarity`, `certificate_no`, `rap`, `discount`, `total`, `price`) 
                    VALUES ('$lotNo','$desc','$shape','$wt','$color','$clarity','$certificate','$rap','$disc', '$total', '$price')";

                    if ($conn->query($sql_insert_stock) === TRUE) {
                        echo 'Data Inserted successfully';
                    } else {
                        echo 'Error: ' . $sql_insert_memo_data . '<br>' . $conn->error;
                    }
                } else {
                    $sql_insert_stock = "UPDATE `stock_list` SET
                    `weight` = `weight` - $wt
                    WHERE `lot_no` = '$lotNo'";

                    if ($conn->query($sql_insert_stock) === TRUE) {
                        echo 'Data Inserted successfully';
                    } else {
                        echo 'Error: ' . $sql_insert_memo_data . '<br>' . $conn->error;
                    }
                }

                if ($conn->query($sql_insert_data) === TRUE) {
                    echo 'Data Inserted successfully';
                } else {
                    echo 'Error: ' . $sql_insert_memo_data . '<br>' . $conn->error;
                }
            }
        }

        // Now, insert the invoice_no into the "invoice_nos" table
        $sql_insert_invoice_no = "INSERT INTO invoice_nos (invoice_no) VALUES ('$invoice_no')";

        if ($conn->query($sql_insert_invoice_no) === TRUE) {
            echo "<p style='color:green; text-align:center;'>Data inserted successfully.</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error inserting invoice_no into invoice_nos: " . $sql_insert_invoice_no . "<br>" . $conn->error . "</p>";
        }

        $conn->close();
    }
}

?>