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

        $lotNumbersInData = array();

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

            // Add the lot number to the array
            $lotNumbersInData[] = $lotNo;

            if (!empty($lotNo)) {
                $sql_check_exists = "SELECT * FROM memo_data WHERE memo_no = '$memo_no' AND lot_no = '$lotNo'";
                $check_result_exists = $conn->query($sql_check_exists);

                if ($check_result_exists->num_rows > 0) {
                    // Fetch the existing 'weight' value from the memo_data table
                    $weight_query = "SELECT kept FROM memo_data WHERE memo_no = '$memo_no' AND lot_no = '$lotNo'";
                    $weight_result = $conn->query($weight_query);

                    if ($weight_result->num_rows > 0) {
                        // Fetch the 'weight' value
                        $row = $weight_result->fetch_assoc();
                        $existingWeight = $row['kept'];

                        if ($wt > $existingWeight) {
                            $diff = $wt - $existingWeight; // Calculate the difference
                            // Update memo_data
                            $sql_update_data = "UPDATE memo_data 
                                                SET `description`='$desc', `shape`='$shape', `size`='$size', `pcs`='$pcs', `weight`='$wt', 
                                                `color`='$color', `clarity`='$clarity', `certificate_no`='$certificate', `rap`='$rap', 
                                                `discount`='$disc', `price`='$price', `total`='$total', `return`='$return', 
                                                `kept`='$kept', `final_total`='$final_total' 
                                                WHERE memo_no='$memo_no' AND lot_no='$lotNo'";
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
                            $diff = $existingWeight - $wt; // Calculate the difference
                            // Update memo_data
                            $sql_update_data = "UPDATE memo_data 
                                    SET `description`='$desc', `shape`='$shape', `size`='$size', `pcs`='$pcs', `weight`='$wt', 
                                    `color`='$color', `clarity`='$clarity', `certificate_no`='$certificate', `rap`='$rap', 
                                    `discount`='$disc', `price`='$price', `total`='$total', `return`='$return', 
                                    `kept`='$kept', `final_total`='$final_total' 
                                    WHERE memo_no='$memo_no' AND lot_no='$lotNo'";
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
                    // Insert a new row into memo_data
                    $sql_insert_data = "INSERT INTO `memo_data`(`memo_no`, `lot_no`, `description`, `shape`, `size`, `pcs`, `weight`, 
                    `color`, `clarity`, `certificate_no`, `rap`, `discount`, `price`, `total`, `return`, 
                    `kept`, `final_total`) 
                    VALUES ('$memo_no','$lotNo','$desc','$shape','$size','$pcs','$wt','$color','$clarity',
                    '$certificate','$rap','$disc','$price','$total', '$return', '$kept', '$final_total')";
                    if ($conn->query($sql_insert_data) === TRUE) {
                        echo 'Data inserted successfully';

                        // Update stock_list by subtracting the weight
                        $update_stock_sql = "UPDATE stock_list SET weight = weight - $wt WHERE lot_no = '$lotNo'";
                        if ($conn->query($update_stock_sql) !== TRUE) {
                            echo 'Error updating stock: ' . $conn->error;
                        }
                    } else {
                        echo 'Error: ' . $sql_insert_data . '<br>' . $conn->error;
                    }
                }

                // Check if the lot number exists in stock_list
                $check_sql = "SELECT * FROM stock_list WHERE lot_no = '$lotNo'";
                $check_result = $conn->query($check_sql);

                if ($check_result->num_rows == 0) {
                    // Lot number doesn't exist, perform an insert
                    $sql_insert_stock = "INSERT INTO `stock_list`(`lot_no`, `shape`, `size`, `pcs`, `weight`, `color`, `clarity`, `certificate_no`, `rap`, `discount`, `total`, `price`) 
                    VALUES ('$lotNo','$shape','$size','$pcs','$wt','$color','$clarity','$certificate','$rap','$disc', '$total', '$price')";
                    
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

        // Fetch existing lot numbers for the given memo_no
        $sql_existing_lot_numbers = "SELECT lot_no FROM memo_data WHERE memo_no = '$memo_no'";
        $result_existing_lot_numbers = $conn->query($sql_existing_lot_numbers);

        if ($result_existing_lot_numbers->num_rows > 0) {
            $existing_lot_numbers = array(); // Array to store existing lot numbers
            while ($row = $result_existing_lot_numbers->fetch_assoc()) {
                $existing_lot_numbers[] = $row['lot_no'];
            }

            // Compare existing lot numbers with the lot numbers passed in the data
            $lot_numbers_to_delete = array_diff($existing_lot_numbers, $lotNumbersInData);

            if (!empty($lot_numbers_to_delete)) {
                // Delete rows from the memo_data table for lot numbers that are not in the data
                $lot_numbers_to_delete_str = implode("', '", $lot_numbers_to_delete);
                // Update stock_list by adding back the weights of the deleted rows
                $deleted_rows_weight = "SELECT `weight`, lot_no FROM memo_data WHERE memo_no = '$memo_no' AND lot_no IN ('$lot_numbers_to_delete_str')";
                $deleted_rows_result = $conn->query($deleted_rows_weight);

                if ($deleted_rows_result->num_rows > 0) {
                    while ($row = $deleted_rows_result->fetch_assoc()) {
                        $deleted_weight = $row['weight'];
                        $deleted_lot_no = $row['lot_no'];

                        $add_deleted_weight_sql = "UPDATE stock_list SET weight = weight + $deleted_weight WHERE lot_no = '$deleted_lot_no'";
                        if ($conn->query($add_deleted_weight_sql) !== TRUE) {
                            echo 'Error updating stock: ' . $conn->error;
                        }
                    }
                }

                $sql_delete_rows = "DELETE FROM memo_data WHERE memo_no = '$memo_no' AND lot_no IN ('$lot_numbers_to_delete_str')";
                if ($conn->query($sql_delete_rows) === TRUE) {
                    // Rows deleted successfully
                    echo "Deleted rows for lot numbers: " . implode(', ', $lot_numbers_to_delete);
                } else {
                    // Handle the deletion error
                    echo "Error deleting rows: " . $conn->error;
                }
            }
        }

        $conn->close();
    }
}
?>