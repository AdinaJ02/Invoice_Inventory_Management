<?php
include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $request_data = json_decode(file_get_contents('php://input'), true);

        $invoice_no = $request_data["invoice_no"];
        $name = $request_data["name"];
        $address = $request_data["address"];
        $total_wt = $request_data["total_wt"];
        $total_final_tot = $request_data["total_final_tot"];
        $disc_total = $request_data["disc_total"];
        $paymentStatus = $request_data["paymentStatus"];
        $data = $request_data["data"];

        // Create an array to store all the lot_no values from the data
        $lotNosFromData = array();
        foreach ($data as $row) {
            $lotNo = (string) $row['lot_no'];
            if (!empty($lotNo)) {
                $lotNosFromData[] = $lotNo;
            }
        }

        $fetch_paymentStatus_sql = "SELECT payment_status FROM invoice_wmemo WHERE invoice_no = '$invoice_no'";
        $paymentStatusResult = $conn->query($fetch_paymentStatus_sql);

        if ($paymentStatusResult->num_rows > 0) {
            $row = $paymentStatusResult->fetch_assoc();
            $paymentStatusInDatabase = $row['payment_status'];
        } else {
            $paymentStatusInDatabase = ''; // Set a default value if no record is found
        }

        // Check if the invoice already exists
        $sql_update_invoice = "UPDATE invoice_wmemo SET customer_name='$name', total_wt='$total_wt', final_total='$total_final_tot', disc_total='$disc_total', payment_status='$paymentStatus' WHERE invoice_no='$invoice_no'";
        if ($conn->query($sql_update_invoice) === TRUE) {
            echo "<p style='color:green; text-align:center;'>Invoice updated successfully.</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error updating invoice: " . $conn->error . "</p>";
        }

        // Iterate through the data and insert or update each row into the database
        foreach ($data as $row) {
            $lotNo = (string) $row['lot_no'];
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
                // Check if the lot_no already exists in invoice_data
                $check_sql_lot = "SELECT * FROM invoice_data WHERE invoice_no = '$invoice_no' AND lot_no = '$lotNo'";
                $check_lot_result = $conn->query($check_sql_lot);

                if ($check_lot_result->num_rows > 0) {
                    // Update the existing row in invoice_data
                    $sql_update_data = "UPDATE invoice_data SET `wt`='$wt', `shape`='$shape', `color`='$color', `clarity`='$clarity', `certificate_no`='$certificate', `rap`='$rap', `discount`='$disc', `price`='$price', `total`='$total' WHERE invoice_no='$invoice_no' AND lot_no='$lotNo'";
                    if ($conn->query($sql_update_data) === TRUE) {
                        echo 'Data updated successfully';

                        if (strcasecmp($paymentStatus, "Received") === 0 && strcasecmp($paymentStatusInDatabase, "Received") !== 0) {
                            // Payment received in the passed data but not received in the database, update stock_list
                            $update_stock_sql = "UPDATE stock_list SET weight = weight - $wt WHERE lot_no = '$lotNo'";
                            if ($conn->query($update_stock_sql) !== TRUE) {
                                // Handle the error more gracefully, e.g., log the error or return a structured response to the client
                                echo 'Error updating stock: ' . $conn->error;
                            }
                        }
                    } else {
                        echo 'Error updating data: ' . $conn->error;
                    }
                } else {
                    // Insert a new row into invoice_data
                    $sql_insert_data = "INSERT INTO `invoice_data`(`invoice_no`, `lot_no`, `wt`, `shape`, `color`, `clarity`, `certificate_no`, `rap`, `discount`, `price`, `total`) 
                        VALUES ('$invoice_no','$lotNo','$wt','$shape','$color','$clarity','$certificate','$rap','$disc','$price','$total')";
                    if ($conn->query($sql_insert_data) === TRUE) {
                        echo 'Data inserted successfully';

                        if (strcasecmp($paymentStatus, "Received") === 0 && strcasecmp($paymentStatusInDatabase, "Received") !== 0) {
                            // Payment received in the passed data but not received in the database, update stock_list
                            $update_stock_sql = "UPDATE stock_list SET weight = weight - $wt WHERE lot_no = '$lotNo'";
                            if ($conn->query($update_stock_sql) !== TRUE) {
                                // Handle the error more gracefully, e.g., log the error or return a structured response to the client
                                echo 'Error updating stock: ' . $conn->error;
                            }
                        }
                    } else {
                        echo 'Error: ' . $sql_insert_data . '<br>' . $conn->error;
                    }
                }
            }
        }

        // Delete rows from invoice_data where lot_no is not in the $lotNosFromData array
        $delete_sql = "DELETE FROM invoice_data WHERE invoice_no = '$invoice_no' AND lot_no NOT IN ('" . implode("','", $lotNosFromData) . "')";
        if ($conn->query($delete_sql) === TRUE) {
            echo 'Rows with lot_no not present in the data were deleted successfully';
        } else {
            echo 'Error deleting rows: ' . $conn->error;
        }

        $conn->close();
    }
}
?>