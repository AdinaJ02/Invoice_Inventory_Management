<?php

require '../../vendor/autoload.php';

include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    $message = '';

    if (isset($_POST['import'])) {
        $fileName = $_FILES['fileToUpload']['name'];
        $file_tmp = $_FILES['fileToUpload']['tmp_name'];
        $file_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Get the file extension

        // Define the allowed file extensions
        $allowed_extensions = array('xls', 'xlsx');

        if (in_array($file_ext, $allowed_extensions)) {
            move_uploaded_file($file_tmp, 'uploads/' . $fileName);

            $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            $spreadSheet = $Reader->load("uploads/" . $fileName);
            $excelSheet = $spreadSheet->getActiveSheet();
            $spreadSheetAry = $excelSheet->toArray();
            $sheetCount = count($spreadSheetAry);


            // Assuming the first row contains headers, you can start from the second row (index 1).
            for ($row = 1; $row < $sheetCount; $row++) {
                $rowData = $spreadSheetAry[$row];

                // Assuming the columns in your Excel file match the database columns in order.
                $lot_no = $rowData[0];
                $shape = $rowData[1];
                $size = $rowData[2];
                $pcs = $rowData[3];
                $Weight = $rowData[4];
                $color = $rowData[5];
                $clarity = $rowData[6];
                $certificate_no = $rowData[7];
                $cut = $rowData[8];
                $pol = $rowData[9];
                $sym = $rowData[10];
                $fl = $rowData[11];
                $m1 = $rowData[12];
                $m2 = $rowData[13];
                $m3 = $rowData[14];
                $tab = $rowData[15];
                $dep = $rowData[16];
                $ratio = $rowData[17];
                $rap = $rowData[18];
                $discount = $rowData[19];
                $total = $rowData[20];
                $price = $rowData[21];
                $name = $rowData[22];
                $avg_Weight = $rowData[23];

                if ($lot_no !== null) {
                    // Define your SQL query using INSERT INTO ... ON DUPLICATE KEY UPDATE
                    $sql = "INSERT INTO stock_list (lot_no, shape, `size`, pcs, `weight`, color, clarity, certificate_no, cut, pol, sym, fl, m1, m2, m3, tab, dep, ratio, rap, discount, total, price, `name`, avg_weight) 
                        VALUES ('$lot_no', '$shape', '$size', '$pcs', '$Weight', '$color', '$clarity', '$certificate_no', '$cut', '$pol', '$sym', '$fl', '$m1', '$m2', '$m3', '$tab', '$dep', '$ratio', '$rap', '$discount', '$total', '$price', '$name', '$avg_Weight') 
                        ON DUPLICATE KEY UPDATE 
                        shape = VALUES(shape), `size` = VALUES(`size`), pcs = VALUES(pcs), `weight` = VALUES(`weight`), color = VALUES(color), 
                        clarity = VALUES(clarity), certificate_no = VALUES(certificate_no), cut = VALUES(cut), pol = VALUES(pol), sym = VALUES(sym), 
                        fl = VALUES(fl), m1 = VALUES(m1), m2 = VALUES(m2), m3 = VALUES(m3), tab = VALUES(tab), dep = VALUES(dep), ratio = VALUES(ratio), 
                        rap = VALUES(rap), discount = VALUES(discount), total = VALUES(total), price = VALUES(price), `name` = VALUES(`name`), avg_weight = VALUES(avg_weight)";

                    if ($conn->query($sql) === TRUE) {
                        $message = "<p style='color:green;'>Record inserted successfully</p>";
                    } else {
                        $message = "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
                    }

                    // Store the imported data in an array
                    $importedData[] = array(
                        'lot_no' => $lot_no,
                        'shape' => $shape,
                        'size' => $size,
                        'pcs' => $pcs,
                        'Weight' => $Weight,
                        'color' => $color,
                        'clarity' => $clarity,
                        'certificate_no' => $certificate_no,
                        'cut' => $cut,
                        'pol' => $pol,
                        'sym' => $sym,
                        'fl' => $fl,
                        'm1' => $m1,
                        'm2' => $m2,
                        'm3' => $m3,
                        'tab' => $tab,
                        'dep' => $dep,
                        'ratio' => $ratio,
                        'rap' => $rap,
                        'discount' => $discount,
                        'total' => $total,
                        'price' => $price,
                        'name' => $name,
                        'avg_Weight' => $avg_Weight,
                    );
                }
            }
        } else {
            $message = "<p style='color:red;'>Invalid file format. Allowed file extensions: xls, xlsx</p>";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock List</title>
    <link rel="stylesheet" href="stock_list.css">
</head>

<body>
    <form method="post" enctype="multipart/form-data">
        Select Excel file to upload:
        <input type="file" name="fileToUpload" id="fileToUpload" class='uploadbutton'>
        <input type="submit" value="Upload File" name="import" class='btn-login' id='do-login'>
        <input type="button" value="Back" onclick="window.history.back()" class='btn-back'>
        <div id='message'>
            <?php echo $message;
            ?>
        </div>
    </form>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Sr No</th>
                <th>Lot No</th>
                <th>Shape</th>
                <th>Size</th>
                <th>Pcs</th>
                <th>Wt (cts)</th>
                <th>Color</th>
                <th>Clarity</th>
                <th>Certificate</th>
                <th>Video</th>
                <th>Cut</th>
                <th>POL</th>
                <th>SYM</th>
                <th>FL</th>
                <th>M1</th>
                <th>M2</th>
                <th>M3</th>
                <th>TAB</th>
                <th>DEP</th>
                <th>Rap ($)</th>
                <th>Dis</th>
                <th>Total</th>
                <th>Price</th>
                <th>Name</th>
                <th>Average weight</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <!-- JavaScript will generate rows here -->
        </tbody>
    </table>

    <script>
        // JavaScript function to populate the table with imported data
        function populateTable(importedData) {
            const tableBody = document.getElementById('table-body');

            // Clear the existing table rows
            tableBody.innerHTML = '';

            // Loop through the imported data and create rows
            for (let i = 0; i < importedData.length; i++) {
                const rowData = importedData[i];
                const newRow = document.createElement('tr');

                newRow.innerHTML = `
                <td>${i + 1}</td>
                <td>${rowData.lot_no}</td>
                <td>${rowData.shape}</td>
                <td>${rowData.size}</td>
                <td>${rowData.pcs}</td>
                <td>${rowData.Weight}</td>
                <td>${rowData.color}</td>
                <td>${rowData.clarity}</td>
                <td>${rowData.certificate_no}</td>
                <td>${rowData.cut}</td>
                <td>${rowData.pol}</td>
                <td>${rowData.sym}</td>
                <td>${rowData.fl}</td>
                <td>${rowData.m1}</td>
                <td>${rowData.m2}</td>
                <td>${rowData.m3}</td>
                <td>${rowData.tab}</td>
                <td>${rowData.dep}</td>
                <td>${rowData.ratio}</td>
                <td>${rowData.rap}</td>
                <td>${rowData.discount}</td>
                <td>${rowData.total}</td>
                <td>${rowData.price}</td>
                <td>${rowData.name}</td>
                <td>${rowData.avg_Weight}</td>
            `;

                tableBody.appendChild(newRow);
            }
        }

        // Call the populateTable function with your imported data
        const importedData = <?php echo json_encode($importedData); ?>;
        populateTable(importedData);
    </script>
</body>

</html>