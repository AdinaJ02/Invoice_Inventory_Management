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
                // Check if the lot_no already exists in invoice_data
                $check_sql_lot = "SELECT * FROM invoice_data WHERE invoice_no = '$invoice_no' AND lot_no = '$lotNo'";
                $check_lot_result = $conn->query($check_sql_lot);

                if ($check_lot_result->num_rows > 0) {
                    // Fetch the existing 'total' value from the invoice_data table
                    $total_query = "SELECT wt FROM invoice_data WHERE invoice_no = '$invoice_no' AND lot_no = '$lotNo'";
                    $total_result = $conn->query($total_query);

                    if ($total_result->num_rows > 0) {
                        // Fetch the 'total' value
                        $row = $total_result->fetch_assoc();
                        $existingTotal = $row['wt']; // This retrieves the 'total' value from the database

                        if ($wt > $existingTotal) {
                            $diff = $wt - $existingTotal; // Calculate the difference
                            // Update invoice_data
                            $sql_update_data = "UPDATE invoice_data 
                                                SET `wt`='$wt', `description`='$desc', `shape`='$shape', `color`='$color', `clarity`='$clarity', `certificate_no`='$certificate', `rap`='$rap', `discount`='$disc', `price`='$price', `total`='$total' 
                                                WHERE invoice_no='$invoice_no' AND lot_no='$lotNo'";
                            if ($conn->query($sql_update_data) === TRUE) {
                                echo 'Data updated successfully';

                                // Update stock_list by subtracting the difference
                                $update_stock_sql = "UPDATE stock_list SET weight = weight - $diff WHERE lot_no = '$lotNo'";
                                if ($conn->query($update_stock_sql) !== TRUE) {
                                    echo 'Error updating stock: ' . $conn->error;
                                }
                            } else {
                                echo 'Error updating data: ' . $conn->error;
                            }
                        } else {
                            $diff = $existingTotal - $wt; // Calculate the difference
                            // Update invoice_data
                            $sql_update_data = "UPDATE invoice_data 
                                                SET `wt`='$wt', `description`='$desc', `shape`='$shape', `color`='$color', `clarity`='$clarity', `certificate_no`='$certificate', `rap`='$rap', `discount`='$disc', `price`='$price', `total`='$total' 
                                                WHERE invoice_no='$invoice_no' AND lot_no='$lotNo'";
                            if ($conn->query($sql_update_data) === TRUE) {
                                echo 'Data updated successfully';

                                // Update stock_list by adding the difference
                                $update_stock_sql = "UPDATE stock_list SET weight = weight + $diff WHERE lot_no = '$lotNo'";
                                if ($conn->query($update_stock_sql) !== TRUE) {
                                    echo 'Error updating stock: ' . $conn->error;
                                }
                            } else {
                                echo 'Error updating data: ' . $conn->error;
                            }
                        }
                    }
                } else {
                    // Insert a new row into invoice_data
                    $sql_insert_data = "INSERT INTO `invoice_data`(`invoice_no`, `lot_no`, `description`, `wt`, `shape`, `color`, `clarity`, `certificate_no`, `rap`, `discount`, `price`, `total`) 
                        VALUES ('$invoice_no','$lotNo','$desc','$wt','$shape','$color','$clarity','$certificate','$rap','$disc','$price','$total')";
                    if ($conn->query($sql_insert_data) === TRUE) {
                        echo 'Data inserted successfully';

                        $update_stock_sql = "UPDATE stock_list SET weight = weight - $wt WHERE lot_no = '$lotNo'";
                        if ($conn->query($update_stock_sql) !== TRUE) {
                            // Handle the error more gracefully, e.g., log the error or return a structured response to the client
                            echo 'Error updating stock: ' . $conn->error;
                        }
                    } else {
                        echo 'Error: ' . $sql_insert_data . '<br>' . $conn->error;
                    }
                }
            }
        }

        // Update stock_list by adding back the weights of the deleted rows
        $deleted_rows_weight = "SELECT wt, lot_no FROM invoice_data WHERE invoice_no = '$invoice_no' AND lot_no NOT IN ('" . implode("','", $lotNosFromData) . "')";
        $deleted_rows_result = $conn->query($deleted_rows_weight);
        
        if ($deleted_rows_result->num_rows > 0) {
            while ($row = $deleted_rows_result->fetch_assoc()) {
                $deleted_weight = $row['wt'];
                $deleted_lot_no = $row['lot_no'];

                $add_deleted_weight_sql = "UPDATE stock_list SET weight = weight + $deleted_weight WHERE lot_no = '$deleted_lot_no'";
                if ($conn->query($add_deleted_weight_sql) !== TRUE) {
                    echo 'Error updating stock: ' . $conn->error;
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