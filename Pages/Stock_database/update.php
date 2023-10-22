<?php
require '../../vendor/autoload.php';
include '../../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedData = json_decode(file_get_contents('php://input'), true);
    var_dump($updatedData);
    // Loop through the updated data and update or insert into the database
    foreach ($updatedData as $row) {
        $lot_no = $row['lot_no'];
        // Check if the lot_no already exists in the database
        $existingRow = $conn->query("SELECT lot_no FROM stock_list WHERE lot_no = '$lot_no'");
        
        if ($existingRow->num_rows > 0) {
            // Update the existing row
            $sql = "UPDATE stock_list SET
                shape = '{$row['shape']}',
                `size` = '{$row['size']}',
                pcs = '{$row['pcs']}',
                `weight` = '{$row['weight']}',
                color = '{$row['color']}',
                clarity = '{$row['clarity']}',
                certificate_no = '{$row['certificate_no']}',
                cut = '{$row['cut']}',
                pol = '{$row['pol']}',
                sym = '{$row['sym']}',
                fl = '{$row['fl']}',
                m1 = '{$row['m1']}',
                m2 = '{$row['m2']}',
                m3 = '{$row['m3']}',
                tab = '{$row['tab']}',
                dep = '{$row['dep']}',
                ratio = '{$row['ratio']}',
                rap = '{$row['rap']}',
                discount = '{$row['discount']}',
                total = '{$row['total']}',
                price = '{$row['price']}',
                `name` = '{$row['name']}',
                avg_Weight = '{$row['avg_Weight']}'
                WHERE lot_no = '$lot_no'";
            
            $result = $conn->query($sql);
        } else {
            // Insert a new row
            $sql = "INSERT INTO stock_list (lot_no, shape, `size`, pcs, `weight`, color, clarity, certificate_no, cut, pol, sym, fl, m1, m2, m3, tab, dep, ratio, rap, discount, total, price, `name`, avg_Weight)
                VALUES (
                    '{$row['lot_no']}',
                    '{$row['shape']}',
                    '{$row['size']}',
                    '{$row['pcs']}',
                    '{$row['weight']}',
                    '{$row['color']}',
                    '{$row['clarity']}',
                    '{$row['certificate_no']}',
                    '{$row['cut']}',
                    '{$row['pol']}',
                    '{$row['sym']}',
                    '{$row['fl']}',
                    '{$row['m1']}',
                    '{$row['m2']}',
                    '{$row['m3']}',
                    '{$row['tab']}',
                    '{$row['dep']}',
                    '{$row['ratio']}',
                    '{$row['rap']}',
                    '{$row['discount']}',
                    '{$row['total']}',
                    '{$row['price']}',
                    '{$row['name']}',
                    '{$row['avg_Weight']}'
                )";
            
            $result = $conn->query($sql);
        }
    }

    if ($result) {
        echo 'Data updated or inserted successfully';
    } else {
        echo 'Error updating or inserting data: ' . $conn->error;
    }
}
?>
